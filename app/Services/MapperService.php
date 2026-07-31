<?php

namespace App\Services;

use App\Models\Mapper;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;

class MapperService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = Mapper::with('user');

        if (isset($filters['status'])) {
            $query->where('mapper_status', $filters['status']);
        }
        if (isset($filters['region'])) {
            $query->where('assigned_region', $filters['region']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Mapper
    {
        return Mapper::with(['user', 'tasks', 'shopLocations', 'vendorRegistrations'])->findOrFail($id);
    }

    public function create(array $data): Mapper
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password123'),
            'phone' => $data['phone'] ?? null,
            'role' => 'mapper',
            'status' => 'active',
        ]);

        return Mapper::create([
            'user_id' => $user->id,
            'employee_id' => $data['employee_id'],
            'assigned_market' => $data['assigned_market'],
            'assigned_region' => $data['assigned_region'],
            'supervisor' => $data['supervisor'] ?? null,
            'mapper_status' => 'active',
        ])->load('user');
    }

    public function update(int $id, array $data): Mapper
    {
        $mapper = Mapper::findOrFail($id);
        $mapper->update($data);

        if ($mapper->user && isset($data['name'])) {
            $mapper->user->update(['name' => $data['name']]);
        }

        return $mapper->load('user');
    }

    public function suspend(int $id): void
    {
        $mapper = Mapper::findOrFail($id);
        $mapper->update(['mapper_status' => 'suspended']);
    }

    public function dashboard(): array
    {
        return [
            'total_mappers' => Mapper::count(),
            'active_mappers' => Mapper::where('mapper_status', 'active')->count(),
            'inactive_mappers' => Mapper::where('mapper_status', 'inactive')->count(),
            'suspended_mappers' => Mapper::where('mapper_status', 'suspended')->count(),
            'total_tasks_assigned' => Task::count(),
            'tasks_completed' => Task::where('status', 'completed')->count(),
            'tasks_pending' => Task::where('status', 'pending')->count(),
            'tasks_rejected' => Task::where('status', 'rejected')->count(),
            'avg_approval_rate' => Mapper::avg('approval_rate') ?? 0,
        ];
    }

    public function assignTask(array $data): Task
    {
        $task = Task::create([
            'mapper_id' => $data['mapper_id'],
            'title' => $data['title'],
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
    }
}
