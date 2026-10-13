<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateCallRoomRequest;
use App\Models\CallInvitation;
use App\Models\ChatConversation;
use App\Models\Notification;
use App\Services\AgoraService;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function __construct(private readonly AgoraService $agora) {}

    public function invite(CreateCallRoomRequest $request)
    {
        $user = $request->user();
        $conversation = $this->conversationForUser($request, $user->id);
        $calleeId = $conversation->admin_id
            ? ($user->role === 'admin' ? $conversation->shop->user_id : $conversation->admin_id)
            : ($conversation->user_id === $user->id ? $conversation->shop->user_id : $conversation->user_id);

        CallInvitation::where('caller_id', $user->id)
            ->where('status', 'ringing')
            ->update(['status' => 'missed']);

        $invitation = CallInvitation::create([
            'conversation_id' => $conversation->id,
            'caller_id' => $user->id,
            'callee_id' => $calleeId,
            'call_type' => $request->validated('call_type'),
            'expires_at' => now()->addSeconds(15),
        ]);

        Notification::create([
            'user_id' => $calleeId,
            'type' => 'call',
            'title' => 'Incoming Call',
            'message' => "You have an incoming {$request->validated('call_type')} call.",
            'link' => "/chat/{$conversation->id}",
        ]);
        Notification::create([
            'user_id' => $user->id,
            'type' => 'call',
            'title' => 'Call Started',
            'message' => "Your {$request->validated('call_type')} call invitation was sent.",
            'link' => "/chat/{$conversation->id}",
        ]);

        return response()->json(['success' => true, 'data' => $this->invitationData($invitation)]);
    }

    public function incoming(Request $request)
    {
        $this->expireInvitations();
        $invitation = CallInvitation::with(['caller', 'conversation'])
            ->where('callee_id', $request->user()->id)
            ->where('status', 'ringing')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        return response()->json(['success' => true, 'data' => $invitation ? $this->invitationData($invitation, true) : null]);
    }

    public function status(Request $request, string $invitation)
    {
        $invitation = $this->findInvitation($invitation);
        abort_unless(in_array((int) $request->user()->id, [(int) $invitation->caller_id, (int) $invitation->callee_id], true), 403);
        $this->expireInvitations();

        return response()->json(['success' => true, 'data' => $this->invitationData($invitation->fresh())]);
    }

    public function accept(Request $request, string $invitation)
    {
        $invitation = $this->findInvitation($invitation);
        abort_unless((int) $invitation->callee_id === (int) $request->user()->id, 403);
        $this->expireInvitations();
        abort_if($invitation->fresh()->status !== 'ringing', 409, 'This call is no longer available.');
        $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);
        Notification::create([
            'user_id' => $invitation->caller_id,
            'type' => 'call',
            'title' => 'Call Accepted',
            'message' => 'Your call was accepted.',
            'link' => "/chat/{$invitation->conversation_id}",
        ]);

        return response()->json(['success' => true, 'data' => $this->invitationData($invitation->fresh())]);
    }

    public function decline(Request $request, string $invitation)
    {
        $invitation = $this->findInvitation($invitation);
        abort_unless((int) $invitation->callee_id === (int) $request->user()->id, 403);
        $invitation->update(['status' => 'declined']);
        Notification::create([
            'user_id' => $invitation->caller_id,
            'type' => 'call',
            'title' => 'Call Declined',
            'message' => 'Your call was declined.',
            'link' => "/chat/{$invitation->conversation_id}",
        ]);

        return response()->json(['success' => true]);
    }

    public function end(Request $request, string $invitation)
    {
        $invitation = $this->findInvitation($invitation);
        abort_unless(in_array((int) $request->user()->id, [(int) $invitation->caller_id, (int) $invitation->callee_id], true), 403);

        if (in_array($invitation->status, ['ringing', 'accepted'], true)) {
            $invitation->update(['status' => 'ended']);
        }

        return response()->json(['success' => true]);
    }

    public function createRoom(CreateCallRoomRequest $request)
    {
        $user = $request->user();
        $conversation = $this->conversationForUser($request, $user->id);

        // The conversation authorization above protects the room name. The HMAC suffix
        // prevents room names from being guessable while keeping both participants aligned.
        $roomName = 'market-mirror-conversation-'.$conversation->id.'-'.substr(
            hash_hmac('sha256', (string) $conversation->id, (string) config('app.key')),
            0,
            24,
        );
        $session = $this->agora->createRtcToken($roomName, (string) $user->id, 'publisher', $request->validated('call_type'));

        return response()->json([
            'success' => true,
            'data' => [
                'app_id' => $session['app_id'],
                'channel' => $roomName,
                'channel_name' => $session['channel_name'],
                'token' => $session['token'],
                'uid' => (string) $user->id,
                'call_type' => $request->validated('call_type'),
            ],
        ]);
    }

    private function conversationForUser(Request $request, int $userId): ChatConversation
    {
        $query = ChatConversation::with('shop')->whereKey($request->validated('conversation_id'));
        if ($request->user()->role !== 'admin') {
            $query->where(fn ($conversation) => $conversation->where('user_id', $userId)->orWhereHas('shop', fn ($shop) => $shop->where('user_id', $userId)));
        }

        return $query
            ->firstOrFail();
    }

    private function expireInvitations(): void
    {
        CallInvitation::where('status', 'ringing')->where('expires_at', '<=', now())->update(['status' => 'missed']);
    }

    private function findInvitation(string $value): CallInvitation
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        abort_unless($id !== false && $id > 0, 404, 'This call invitation is invalid.');

        $invitation = CallInvitation::find($id);

        abort_unless($invitation, 404, 'This call invitation no longer exists.');

        return $invitation;
    }

    private function invitationData(CallInvitation $invitation, bool $includeCaller = false): array
    {
        return [
            'id' => $invitation->id,
            'conversation_id' => $invitation->conversation_id,
            'call_type' => $invitation->call_type,
            'status' => $invitation->status,
            'expires_at' => $invitation->expires_at?->toISOString(),
            'caller_name' => $includeCaller ? $invitation->caller?->name : null,
        ];
    }
}
