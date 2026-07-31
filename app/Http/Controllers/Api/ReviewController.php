<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService
    ) {}

    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'reply' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->reviewService->reply($id, $validated['reply']),
            'message' => 'Reply posted',
        ]);
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->reviewService->list(
                $request->only(['shop_id']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->reviewService->create($validated, $request->user()->id, $request->user()->name),
            'message' => 'Review created',
        ], 201);
    }

    public function destroy($id)
    {
        $this->reviewService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted',
        ]);
    }
}
