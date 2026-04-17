<?php

namespace App\Repositories\Eloquent;

use App\Models\Column;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAllForColumn(Column $column): Collection
    {
        return $column->tasks()->orderBy('position')->orderBy('id')->get();
    }

    public function createForColumn(Column $column, array $attributes): Task
    {
        if (! array_key_exists('position', $attributes)) {
            $attributes['position'] = ((int) $column->tasks()->max('position')) + 1;
        }

        return $column->tasks()->create($attributes);
    }

    public function findForColumnByIdOrFail(Column $column, int $taskId): Task
    {
        return $column->tasks()->whereKey($taskId)->firstOrFail();
    }

    public function findForUserByIdOrFail(User $user, int $taskId): Task
    {
        return Task::query()
            ->whereKey($taskId)
            ->whereHas('column.board', function ($query) use ($user) {
                $query->where(function ($boardQuery) use ($user) {
                    $boardQuery->where('user_id', $user->id)
                        ->orWhereHas('members', function ($memberQuery) use ($user) {
                            $memberQuery->where('user_id', $user->id);
                        });
                });
            })
            ->firstOrFail();
    }

    public function update(Task $task, array $attributes): Task
    {
        $safeAttributes = array_diff_key($attributes, array_flip(['column_id']));
        $task->update($safeAttributes);

        return $task->refresh();
    }

    public function move(Task $task, Column $targetColumn, int $position): Task
    {
        return DB::transaction(function () use ($task, $targetColumn, $position): Task {
            $task->loadMissing('column');

            $sourceColumn = $task->column;
            $targetPosition = max(1, $position);
            $maxPosition = $sourceColumn->is($targetColumn)
                ? ((int) $targetColumn->tasks()->whereKeyNot($task->id)->count()) + 1
                : ((int) $targetColumn->tasks()->count()) + 1;

            $targetPosition = min($targetPosition, $maxPosition);

            $sourceColumn->tasks()
                ->where('position', '>', $task->position)
                ->decrement('position');

            $targetColumn->tasks()
                ->where('position', '>=', $targetPosition)
                ->when($sourceColumn->is($targetColumn), function ($query) use ($task) {
                    $query->whereKeyNot($task->id);
                })
                ->increment('position');

            $task->column()->associate($targetColumn);
            $task->position = $targetPosition;
            $task->save();

            return $task->refresh();
        });
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
