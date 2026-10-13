<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $notifications = $this->notificationService->list(
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(NotificationResource::collection($notifications), $notifications);
    }

    public function update(UpdateNotificationRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new NotificationResource(
                $this->notificationService->markRead((int) $id, $request->user()->id)
            ),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $this->notificationService->markAllRead($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}
