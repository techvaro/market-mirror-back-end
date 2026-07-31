<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function list(array $filters, int $perPage = 15)
    {
        $query = User::query();

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): User
    {
        return User::with(['shops', 'orders', 'mapperProfile'])->findOrFail($id);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function suspend(int $id): User
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'suspended']);
        return $user;
    }

    public function updateStatus(int $id, string $status): User
    {
        $user = User::findOrFail($id);
        $user->update(['status' => $status]);
        return $user;
    }
}
