<?php

namespace App\Policies;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\Column;
use App\Models\User;

class ColumnPolicy
{
    public function viewAny(User $user, Board $board): bool
    {
        return $board->userIsAtLeast($user, BoardRole::Member);
    }

    public function view(User $user, Column $column): bool
    {
        $column->loadMissing('board');

        return $column->board !== null
            && $column->board->userIsAtLeast($user, BoardRole::Member);
    }

    public function create(User $user, Board $board): bool
    {
        return $board->userIsAtLeast($user, BoardRole::Admin);
    }

    public function update(User $user, Column $column): bool
    {
        $column->loadMissing('board');

        return $column->board !== null
            && $column->board->userIsAtLeast($user, BoardRole::Admin);
    }

    public function delete(User $user, Column $column): bool
    {
        return $this->update($user, $column);
    }
}
