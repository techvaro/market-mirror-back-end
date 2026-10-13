<?php

namespace App\Services;

use App\Models\State;

class StateService
{
    public function activeList()
    {
        return State::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with('markets')
            ->get();
    }

    public function all(int $perPage = 15)
    {
        return State::with('markets')->latest()->paginate($perPage);
    }

    public function find(int $id): State
    {
        return State::with('markets')->findOrFail($id);
    }

    public function create(array $data): State
    {
        return State::create($data);
    }

    public function update(int $id, array $data): State
    {
        $state = State::findOrFail($id);
        $state->update($data);

        return $state;
    }

    public function setActive(int $id, bool $active): State
    {
        $state = State::findOrFail($id);
        $state->update(['is_active' => $active]);

        return $state;
    }

    public function delete(int $id): void
    {
        State::findOrFail($id)->delete();
    }
}
