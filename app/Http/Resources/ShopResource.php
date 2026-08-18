<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'market_id' => $this->market_id,
            'market_details' => $this->when(
                $this->resource->relationLoaded('market'),
                fn () => new MarketResource($this->resource->getRelation('market'))
            ),
            'name' => $this->name,
            'category' => $this->category,
            'rating' => $this->rating,
            'review_count' => $this->review_count,
            'product_count' => $this->product_count,
            'description' => $this->description,
            'location' => $this->location,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'hours' => $this->hours,
            'banner_gradient' => $this->banner_gradient,
            'market' => $this->getAttribute('market'),
            'city' => $this->city,
            'phone' => $this->phone,
            'shop_number' => $this->shop_number,
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'images' => ShopImageResource::collection($this->whenLoaded('shopImages')),
            'created_at' => $this->created_at,
        ];
    }
}
