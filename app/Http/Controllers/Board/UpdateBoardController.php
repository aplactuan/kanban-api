<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;

class UpdateBoardController extends Controller
{
    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(UpdateBoardRequest $request, int $board): BoardResource
    {
        /** @var array{name?: string, description?: string|null} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $updatedBoard = $this->boardRepository->update($existingBoard, $validated);

        return new BoardResource($updatedBoard);
    }
}
