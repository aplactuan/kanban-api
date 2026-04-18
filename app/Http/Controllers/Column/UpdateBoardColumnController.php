<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBoardColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\Board;
use App\Models\Column;
use App\Repositories\Contracts\ColumnRepositoryInterface;

class UpdateBoardColumnController extends Controller
{
    public function __construct(
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(UpdateBoardColumnRequest $request, Board $board, Column $column): ColumnResource
    {
        /** @var array{name?: string, position?: int} $validated */
        $validated = $request->validated();

        $this->authorize('update', $column);

        $updatedColumn = $this->columnRepository->update($column, $validated);

        return new ColumnResource($updatedColumn);
    }
}
