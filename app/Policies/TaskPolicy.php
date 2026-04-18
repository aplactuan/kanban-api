<?php

namespace App\Policies;

use App\Enums\BoardRole;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Column $column): bool
    {
        $column->loadMissing('board');

        return $column->board !== null
            && $column->board->userIsAtLeast($user, BoardRole::Member);
    }

    public function view(User $user, Task $task): bool
    {
        $task->loadMissing('column.board');

        return $task->column !== null
            && $task->column->board !== null
            && $task->column->board->userIsAtLeast($user, BoardRole::Member);
    }

    public function create(User $user, Column $column): bool
    {
        return $this->viewAny($user, $column);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function move(User $user, Task $task, Column $targetColumn): bool
    {
        if (! $this->view($user, $task)) {
            return false;
        }

        $task->loadMissing('column.board');
        $board = $task->column?->board;

        if ($board === null) {
            return false;
        }

        $targetColumn->loadMissing('board');

        return $targetColumn->board_id === $board->id;
    }
}
