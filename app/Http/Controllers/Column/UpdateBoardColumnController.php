<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBoardColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;

class UpdateBoardColumnController extends Controller
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(UpdateBoardColumnRequest $request, int $board, int $column): ColumnResource
    {
        /** @var array{name?: string, position?: int} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $existingColumn = $this->columnRepository->findForBoardByIdOrFail($existingBoard, $column);
        $updatedColumn = $this->columnRepository->update($existingColumn, $validated);

        return new ColumnResource($updatedColumn);
    }
}
