<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MapperResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'employee_id' => $this->employee_id,
            'assigned_market' => $this->assigned_market,
            'assigned_region' => $this->assigned_region,
            'mapper_status' => $this->mapper_status,
            'gps_status' => $this->gps_status,
            'tasks_assigned' => $this->tasks_assigned,
            'tasks_completed' => $this->tasks_completed,
            'tasks_pending' => $this->tasks_pending,
            'tasks_rejected' => $this->tasks_rejected,
            'approval_rate' => $this->approval_rate,
            'avg_completion_time_hrs' => $this->avg_completion_time_hrs,
            'distance_covered_km' => $this->distance_covered_km,
            'supervisor' => $this->supervisor,
            'notes' => $this->notes,
            'last_login_at' => $this->last_login_at,
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'created_at' => $this->created_at,
        ];
    }
}
