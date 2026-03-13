<?php

namespace App\Repositories\Contracts;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Database\Eloquent\Collection;

interface ColumnRepositoryInterface
{
    /**
     * @return Collection<int, Column>
     */
    public function getAllForBoard(Board $board): Collection;

    /**
     * @param  array{name: string, position?: int}  $attributes
     */
    public function createForBoard(Board $board, array $attributes): Column;

    public function findForBoardByIdOrFail(Board $board, int $columnId): Column;

    /**
     * @param  array{name?: string, position?: int}  $attributes
     */
    public function update(Column $column, array $attributes): Column;

    public function delete(Column $column): void;
}
