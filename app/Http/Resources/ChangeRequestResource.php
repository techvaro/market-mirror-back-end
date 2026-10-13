<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChangeRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'entityType' => $this->entity_type,
            'entityId' => $this->entity_id,
            'entityName' => $this->entity_name,
            'fieldChanges' => $this->field_changes,
            'reason' => $this->reason,
            'status' => $this->status,
            'adminNote' => $this->admin_note,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'user' => $this->whenLoaded('user', fn () => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
        ];
    }
}
