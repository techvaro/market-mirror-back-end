<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'user_id' => $this->user_id,
            'reviewer_name' => $this->reviewer_name,
            'rating' => (int) $this->rating,
            'text' => $this->text,
            'reply' => $this->reply,
            'images' => $this->whenLoaded('reviewImages', fn () => $this->reviewImages->pluck('image_url')),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
