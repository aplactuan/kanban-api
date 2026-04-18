<?php

namespace App\Repositories\Contracts;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface BoardRepositoryInterface
{
    /**
     * @return Collection<int, Board>
     */
    public function getAllForUser(User $user): Collection;

    /**
     * @param  array{name: string, description?: string|null}  $attributes
     */
    public function createForUser(User $user, array $attributes): Board;

    public function findByIdOrFail(int $boardId): Board;

    /**
     * @param  array{name?: string, description?: string|null}  $attributes
     */
    public function update(Board $board, array $attributes): Board;

    public function delete(Board $board): void;
}
