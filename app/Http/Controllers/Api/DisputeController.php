<?php

namespace App\Http\Controllers\Api;

use App\Actions\Disputes\ResolveDisputeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactDisputeVendorRequest;
use App\Http\Requests\Api\ResolveDisputeRequest;
use App\Http\Requests\Api\StoreDisputeRequest;
use App\Http\Requests\Api\UpdateDisputeRequest;
use App\Http\Resources\DisputeResource;
use App\Models\CallInvitation;
use App\Models\ChatConversation;
use App\Models\Dispute;
use App\Models\Notification;
use App\Services\DisputeService;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService,
        private readonly ResolveDisputeAction $resolveDisputeAction
    ) {}

    public function index(Request $request)
    {
        $disputes = $this->disputeService->list(
            $request->only(['status']),
            $request->user()->role,
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(DisputeResource::collection($disputes), $disputes);
    }

    public function store(StoreDisputeRequest $request)
    {
        $dispute = $this->disputeService->create($request->sanitized(), $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => new DisputeResource($dispute->load('order', 'user')),
            'message' => 'Dispute filed',
        ], 201);
    }

    public function update(UpdateDisputeRequest $request, $id)
    {
        $dispute = $this->disputeService->find($id);

        $this->authorize('update', $dispute);

        return response()->json([
            'success' => true,
            'data' => new DisputeResource($this->disputeService->update($id, $request->sanitized())->load('order', 'user')),
            'message' => 'Dispute updated',
        ]);
    }

    public function resolve(ResolveDisputeRequest $request, $id)
    {
        $dispute = $this->disputeService->find($id);

        $this->authorize('resolve', Dispute::class);

        $resolved = $this->resolveDisputeAction->execute(
            $dispute,
            $request->validated('action'),
            $request->user(),
            $request->validated('note')
        );

        return response()->json([
            'success' => true,
            'data' => new DisputeResource($resolved),
            'message' => 'Dispute resolved',
        ]);
    }

    public function contactVendor(ContactDisputeVendorRequest $request, $id)
    {
        $dispute = $this->disputeService->find((int) $id);
        $this->authorize('resolve', Dispute::class);

        $sent = $this->disputeService->contactVendors(
            $dispute,
            $request->user(),
            $request->validated('message')
        );

        return response()->json([
            'success' => true,
            'data' => ['vendors_contacted' => $sent],
            'message' => $sent > 0 ? 'Message sent to the vendor(s)' : 'No vendor was found for this order',
        ]);
    }

    public function callVendor(Request $request, $id)
    {
        $request->validate(['call_type' => ['required', 'in:audio,video']]);
        $dispute = $this->disputeService->find((int) $id);
        $this->authorize('resolve', Dispute::class);
        $dispute->loadMissing('order.items.product.shop');

        $invitations = 0;
        $shops = $dispute->order?->items->map(fn ($item) => $item->product?->shop)->filter()->unique('id') ?? collect();
        foreach ($shops as $shop) {
            $conversation = ChatConversation::firstOrCreate([
                'user_id' => $dispute->user_id,
                'shop_id' => $shop->id,
            ]);
            CallInvitation::where('caller_id', $request->user()->id)->where('status', 'ringing')->update(['status' => 'missed']);
            CallInvitation::create([
                'conversation_id' => $conversation->id,
                'caller_id' => $request->user()->id,
                'callee_id' => $shop->user_id,
                'call_type' => $request->validated('call_type'),
                'expires_at' => now()->addSeconds(30),
            ]);
            Notification::create([
                'user_id' => $shop->user_id,
                'type' => 'call',
                'title' => 'Incoming Admin Call',
                'message' => "An admin is requesting a {$request->validated('call_type')} call about a dispute.",
                'link' => "/chat/{$conversation->id}",
            ]);
            Notification::create([
                'user_id' => $request->user()->id,
                'type' => 'call',
                'title' => 'Admin Call Request Sent',
                'message' => "Your {$request->validated('call_type')} call request was sent to the vendor.",
                'link' => '/disputes',
            ]);
            $invitations++;
        }

        return response()->json(['success' => true, 'data' => ['vendors_contacted' => $invitations]]);
    }
}
