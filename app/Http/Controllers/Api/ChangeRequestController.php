<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChangeRequest;
use App\Http\Requests\Api\UpdateChangeRequest;
use App\Http\Resources\ChangeRequestResource;
use App\Models\ChangeRequest;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = ChangeRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => ChangeRequestResource::collection($requests),
        ]);
    }

    public function store(StoreChangeRequest $request)
    {
        $data = $request->validated();
        $entity = $data['entity_type'] === 'shop'
            ? Shop::findOrFail($data['entity_id'])
            : Product::findOrFail($data['entity_id']);

        abort_unless($entity->user_id === $request->user()->id || ($entity instanceof Product && $entity->shop?->user_id === $request->user()->id), 403);

        $changeRequest = ChangeRequest::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        User::where('role', 'admin')->get()->each(function (User $admin) use ($changeRequest) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'change_request',
                'title' => 'Shop changes awaiting approval',
                'message' => "{$changeRequest->entity_name} has submitted changes for review.",
                'read' => false,
                'link' => '/approvals?tab=shop-changes',
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => new ChangeRequestResource($changeRequest),
            'message' => 'Changes submitted for admin approval.',
        ], 201);
    }

    public function adminIndex()
    {
        return response()->json([
            'success' => true,
            'data' => ChangeRequestResource::collection(
                ChangeRequest::with('user')->where('status', 'pending')->latest()->get()
            ),
        ]);
    }

    public function adminUpdate(UpdateChangeRequest $request, int $id)
    {
        $changeRequest = ChangeRequest::findOrFail($id);

        if ($changeRequest->status !== 'pending') {
            return response()->json(['message' => 'This change request has already been decided.'], 422);
        }

        if ($request->validated('status') === 'approved') {
            $this->applyChanges($changeRequest);
        }

        $changeRequest->update([
            'status' => $request->validated('status'),
            'admin_note' => $request->validated('admin_note'),
        ]);

        Notification::create([
            'user_id' => $changeRequest->user_id,
            'type' => 'change_request_'.$changeRequest->status,
            'title' => 'Shop changes '.$changeRequest->status,
            'message' => $changeRequest->status === 'approved'
                ? 'Your shop changes have been approved and are now live.'
                : 'Your shop changes were rejected. Check the review note for details.',
            'read' => false,
            'link' => '/shop',
        ]);

        return response()->json([
            'success' => true,
            'data' => new ChangeRequestResource($changeRequest),
        ]);
    }

    private function applyChanges(ChangeRequest $changeRequest): void
    {
        $entity = $changeRequest->entity_type === 'shop'
            ? Shop::findOrFail($changeRequest->entity_id)
            : Product::findOrFail($changeRequest->entity_id);
        $updates = [];

        foreach ($changeRequest->field_changes as $change) {
            $field = $change['field'];
            $value = $change['newValue'] ?? null;
            $fieldMap = [
                'location.marketName' => 'market',
                'location.address' => 'location',
                'location.city' => 'city',
                'location.stallNumber' => 'shop_number',
                'location.gpsLat' => 'latitude',
                'location.gpsLng' => 'longitude',
            ];
            $updates[$fieldMap[$field] ?? $field] = $value;
        }

        $allowed = $entity instanceof Shop
            ? ['name', 'description', 'category', 'phone', 'location', 'market', 'city', 'shop_number', 'latitude', 'longitude']
            : ['name', 'description', 'category', 'price', 'stock'];
        $entity->update(array_intersect_key($updates, array_flip($allowed)));
    }
}
