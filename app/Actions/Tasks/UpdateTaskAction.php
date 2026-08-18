<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Updates the status of a mapper task and keeps the mapper counters in sync.
 */
class UpdateTaskAction
{
    public function execute(int $id, array $data): Task
    {
        return DB::transaction(function () use ($id, $data) {
            $task = Task::findOrFail($id);
            $previousStatus = $task->status;
            $nextStatus = $data['status'] ?? $previousStatus;

            $task->update($data);

            $mapper = $task->mapper;
            if ($mapper && $previousStatus !== $nextStatus) {
                $this->adjustMapperCounters($mapper, $previousStatus, $nextStatus);

                if ($nextStatus === 'completed') {
                    $task->update(['completed_at' => now()]);
                    $mapper->refresh()->update(['approval_rate' => $this->recalculateApprovalRate($mapper)]);
                }
            }

            return $task->load('mapper');
        });
    }

    private function adjustMapperCounters(Mapper $mapper, string $from, string $to): void
    {
        $decrements = ['pending' => 'tasks_pending', 'completed' => 'tasks_completed', 'rejected' => 'tasks_rejected'];
        $increments = ['pending' => 'tasks_pending', 'completed' => 'tasks_completed', 'rejected' => 'tasks_rejected'];

        if (isset($decrements[$from])) {
            $mapper->decrement($decrements[$from]);
        }
        if (isset($increments[$to])) {
            $mapper->increment($increments[$to]);
        }
    }

    private function recalculateApprovalRate(Mapper $mapper): float
    {
        $completed = $mapper->tasks_completed;
        $rejected = $mapper->tasks_rejected;
        $total = $completed + $rejected;

        if ($total === 0) {
            return 100;
        }

        return round($completed / $total * 100, 1);
    }
}
