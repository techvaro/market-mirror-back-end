<?php

namespace App\Policies;

use App\Models\Mapper;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function manage(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, ?Task $task = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($task === null) {
            return $user->role === 'mapper';
        }

        return $task->mapper_id === $user->mapperProfile?->id;
    }

    public function update(User $user, ?Task $task = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($task === null) {
            return $user->role === 'mapper';
        }

        return $task->mapper_id === $user->mapperProfile?->id;
    }
}
