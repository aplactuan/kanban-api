<?php

namespace App\Repositories\Eloquent;

use App\Models\Board;
use App\Models\Column;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ColumnRepository implements ColumnRepositoryInterface
{
    public function getAllForBoard(Board $board): Collection
    {
        return $board->columns()->orderBy('position')->orderBy('id')->get();
    }

    public function createForBoard(Board $board, array $attributes): Column
    {
        if (! array_key_exists('position', $attributes)) {
            $attributes['position'] = ((int) $board->columns()->max('position')) + 1;
        }

        return $board->columns()->create($attributes);
    }

    public function findForBoardByIdOrFail(Board $board, int $columnId): Column
    {
        return $board->columns()->whereKey($columnId)->firstOrFail();
    }

    public function update(Column $column, array $attributes): Column
    {
        $safeAttributes = array_diff_key($attributes, array_flip(['board_id']));
        $column->update($safeAttributes);

        return $column->refresh();
    }

    public function delete(Column $column): void
    {
        $column->delete();
    }
}
