<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function list(int $userId, int $perPage = 15)
    {
        return Notification::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function markRead(int $id, int $userId): Notification
    {
        $notification = Notification::where('user_id', $userId)->findOrFail($id);
        $notification->update(['read' => true]);
        return $notification;
    }
}
