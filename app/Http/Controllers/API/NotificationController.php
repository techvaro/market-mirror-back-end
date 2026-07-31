<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markRead(Request $request, \App\Models\Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $notification->update(['read' => true]);

        return response()->json($notification);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->where('read', false)->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json(['message' => 'All notifications cleared']);
    }
}
