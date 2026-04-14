<?php

namespace App\Repositories\Eloquent;

use App\Models\Board;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BoardRepository implements BoardRepositoryInterface
{
    public function getAllForUser(User $user): Collection
    {
        return $user->boards()->latest()->get();
    }

    public function createForUser(User $user, array $attributes): Board
    {
        $board = $user->boards()->create($attributes);
        $board->members()->attach($user->id, ['role' => 'owner']);

        return $board;
    }

    public function findForUserByIdOrFail(User $user, int $boardId): Board
    {
        return $user->boards()->whereKey($boardId)->firstOrFail();
    }

    public function update(Board $board, array $attributes): Board
    {
        $safeAttributes = array_diff_key($attributes, array_flip(['user_id']));
        $board->update($safeAttributes);

        return $board->refresh();
    }

    public function delete(Board $board): void
    {
        $board->delete();
    }
}
