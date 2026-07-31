<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationService->list(
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationService->markRead($id, $request->user()->id),
        ]);
    }
}
