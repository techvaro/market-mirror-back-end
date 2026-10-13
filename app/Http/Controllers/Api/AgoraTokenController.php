<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AgoraTokenRequest;
use App\Services\AgoraService;
use InvalidArgumentException;

class AgoraTokenController extends Controller
{
    public function __construct(private readonly AgoraService $agora) {}

    public function __invoke(AgoraTokenRequest $request)
    {
        $data = $request->validated();

        abort_unless((string) $data['uid'] === (string) $request->user()->id, 403, 'The Agora UID must belong to the authenticated user.');

        try {
            return response()->json([
                'success' => true,
                'data' => $this->agora->createRtcToken(
                    (string) $data['channel_name'],
                    (string) $data['uid'],
                    (string) $data['role'],
                    (string) $data['call_type'],
                ),
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 503);
        }
    }
}
