<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Mapper;

class TaskService
{
    public function list(array $filters, ?int $userId = null, int $perPage = 15)
    {
        $query = Task::with('mapper.user');

        if ($userId) {
            $mapper = Mapper::where('user_id', $userId)->first();
            if ($mapper) {
                $query->where('mapper_id', $mapper->id);
            }
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['mapper_id'])) {
            $query->where('mapper_id', $filters['mapper_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Task
    {
        return Task::with(['mapper.user', 'vendor'])->findOrFail($id);
    }

    public function create(array $data): Task
    {
        return Task::create([
            ...$data,
            'assigned_at' => now(),
            'status' => 'pending',
        ]);
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);

        if (isset($data['status'])) {
            $this->handleStatusTransition($task, $data['status']);
            unset($data['status']);
        }

        if (!empty($data)) {
            $task->update($data);
        }

        return $task;
    }

    private function handleStatusTransition(Task $task, string $newStatus): void
    {
        $oldStatus = $task->status;

        $task->update(['status' => $newStatus]);

        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $task->update(['completed_at' => now()]);
            $this->updateMapperCounters($task->mapper_id, 'completed');
        } elseif ($newStatus === 'rejected' && $oldStatus !== 'rejected') {
            $this->updateMapperCounters($task->mapper_id, 'rejected');
        } elseif ($newStatus === 'in_progress' && $oldStatus === 'pending') {
            $this->updateMapperCounters($task->mapper_id, 'in_progress');
        }
    }

    private function updateMapperCounters(int $mapperId, string $transition): void
    {
        $mapper = Mapper::find($mapperId);
        if (!$mapper) return;

        match ($transition) {
            'completed' => [
                $mapper->increment('tasks_completed'),
                $mapper->decrement('tasks_pending'),
            ],
            'rejected' => [
                $mapper->increment('tasks_rejected'),
                $mapper->decrement('tasks_pending'),
            ],
            'in_progress' => [
                $mapper->decrement('tasks_pending'),
            ],
        };
    }
}
