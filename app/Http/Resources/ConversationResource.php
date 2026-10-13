<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'admin_id' => $this->admin_id,
            'admin' => UserResource::make($this->whenLoaded('admin')),
            'shop_id' => $this->shop_id,
            'shop' => ShopResource::make($this->whenLoaded('shop')),
            'vendor' => $this->when($this->relationLoaded('shop'), function () {
                return $this->shop?->user ? [
                    'id' => $this->shop->user->id,
                    'name' => $this->shop->user->name,
                    'email' => $this->shop->user->email,
                    'phone' => $this->shop->user->phone,
                    'avatar_url' => $this->shop->user->avatar_url,
                    'profile_image_url' => $this->shop->user->profile_image_url,
                ] : null;
            }),
            'latest_message' => $this->whenLoaded(
                'messages',
                fn () => MessageResource::make($this->messages->first())
            ),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
