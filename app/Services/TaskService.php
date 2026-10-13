<?php

namespace App\Services;

use App\Models\Mapper;
use App\Models\Notification;
use App\Models\Task;

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
        return Task::with('mapper.user')->findOrFail($id);
    }

    public function create(array $data): Task
    {
        $task = Task::create([
            ...$data,
            'assigned_at' => now(),
            'status' => 'pending',
        ]);

        $mapper = Mapper::find($task->mapper_id);
        if ($mapper) {
            Notification::create([
                'user_id' => $mapper->user_id,
                'type' => 'assignment',
                'title' => 'New Task Assigned',
                'message' => "You have been assigned a new task: \"{$task->title}\" in {$task->market}."
                    .($task->due_date ? ' Due '.$task->due_date->format('M j, Y').'.' : ''),
                'link' => "/tasks/{$task->id}",
            ]);
        }

        return $task;
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);

        if (isset($data['status'])) {
            $this->handleStatusTransition($task, $data['status']);
            unset($data['status']);
        }

        if (! empty($data)) {
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
        if (! $mapper) {
            return;
        }

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
