<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Repositories\Contracts\BoardRepositoryInterface;

class UpdateBoardController extends Controller
{
    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(UpdateBoardRequest $request, Board $board): BoardResource
    {
        /** @var array{name?: string, description?: string|null} $validated */
        $validated = $request->validated();

        $this->authorize('update', $board);

        $updatedBoard = $this->boardRepository->update($board, $validated);

        return new BoardResource($updatedBoard);
    }
}
