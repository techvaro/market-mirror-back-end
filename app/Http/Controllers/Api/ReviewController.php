<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReplyReviewRequest;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Http\Requests\Api\UploadReviewImageRequest;
use App\Http\Resources\ReviewResource;
use App\Models\ReviewImage;
use App\Services\ReviewService;
use App\Services\UploadService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
        private readonly UploadService $uploadService
    ) {}

    public function index(Request $request)
    {
        $reviews = $this->reviewService->list(
            $request->only(['shop_id']),
            $request->get('per_page', 15)
        );

        return $this->paginated(ReviewResource::collection($reviews), $reviews);
    }

    public function store(StoreReviewRequest $request)
    {
        $review = $this->reviewService->create(
            $request->sanitized(),
            $request->user()->id,
            $request->user()->name
        );

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review->load('shop', 'user')),
            'message' => 'Review created',
        ], 201);
    }

    public function reply(ReplyReviewRequest $request, $id)
    {
        $review = $this->reviewService->find($id);

        $this->authorize('reply', $review);

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($this->reviewService->reply($id, $request->validated('reply'))->load('shop', 'user')),
            'message' => 'Reply posted',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $review = $this->reviewService->find($id);

        $this->authorize('delete', $review);

        $this->reviewService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted',
        ]);
    }

    public function uploadImage(UploadReviewImageRequest $request, $id)
    {
        $review = $this->reviewService->find($id);

        $this->authorize('uploadImage', $review);

        $url = $this->uploadService->store($request->file('image'), 'reviews');

        ReviewImage::create([
            'review_id' => $review->id,
            'image_url' => $url,
        ]);

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review->load('reviewImages', 'shop', 'user')),
            'message' => 'Review image uploaded',
        ], 201);
    }
}
