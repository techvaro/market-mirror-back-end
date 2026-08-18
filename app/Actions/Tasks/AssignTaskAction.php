<?php

namespace App\Actions\Tasks;

use App\Models\Mapper;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

/**
 * Assigns a new field task to a mapper and keeps the mapper counters in sync.
 */
class AssignTaskAction
{
    public function execute(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = Task::create([
                'mapper_id' => $data['mapper_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'market' => $data['market'],
                'priority' => $data['priority'],
                'due_date' => $data['due_date'],
                'assigned_at' => now(),
                'status' => 'pending',
            ]);

            $mapper = Mapper::find($data['mapper_id']);
            if ($mapper) {
                $mapper->increment('tasks_assigned');
                $mapper->increment('tasks_pending');
            }

            return $task;
        });
    }
}
