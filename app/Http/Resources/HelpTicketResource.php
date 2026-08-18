<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HelpTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'category' => $this->category,
            'status' => $this->status,
            'response' => $this->response,
            'created_at' => $this->created_at,
        ];
    }
}
