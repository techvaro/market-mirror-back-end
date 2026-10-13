<?php

namespace App\Services;

use App\Models\Mapper;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Models\VendorRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password123'),
                'phone' => $data['phone'] ?? null,
                'role' => 'mapper',
                'status' => 'active',
                'email_verified_at' => now(),
                'must_change_password' => true,
            ]);

            return Mapper::create([
                'user_id' => $user->id,
                'employee_id' => $data['employee_id'],
                'assigned_market' => $data['assigned_market'],
                'assigned_region' => $data['assigned_region'],
                'supervisor' => $data['supervisor'] ?? null,
                'mapper_status' => 'active',
            ])->load('user');
        });
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
        $dailyRegistrations = collect(range(0, 6))->map(function (int $daysAgo) {
            $date = Carbon::now()->subDays(6 - $daysAgo);

            return [
                'date' => $date->format('D'),
                'count' => VendorRegistration::whereDate('created_at', $date)->count(),
            ];
        })->values()->all();

        $weeklyRegistrations = collect(range(0, 3))->map(function (int $weeksAgo) {
            $end = Carbon::now()->subWeeks(3 - $weeksAgo)->endOfWeek();
            $start = $end->copy()->subDays(6)->startOfDay();

            return [
                'date' => $start->format('M j'),
                'count' => VendorRegistration::whereBetween('created_at', [$start, $end])->count(),
            ];
        })->values()->all();

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
            'daily_registrations' => $dailyRegistrations,
            'weekly_registrations' => $weeklyRegistrations,
            'top_mappers' => Mapper::with('user')->orderByDesc('tasks_completed')->limit(5)->get()->map(fn (Mapper $mapper) => [
                'id' => $mapper->id,
                'name' => $mapper->user?->name ?? 'Unknown',
                'total_scans' => $mapper->tasks_completed,
                'status' => $mapper->mapper_status,
                'image_url' => $mapper->user?->avatar_url,
            ])->values()->all(),
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
}
