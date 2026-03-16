<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DestroyColumnTaskController extends Controller
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private ColumnRepositoryInterface $columnRepository,
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(Request $request, int $board, int $column, int $task): Response
    {
        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $existingColumn = $this->columnRepository->findForBoardByIdOrFail($existingBoard, $column);
        $existingTask = $this->taskRepository->findForColumnByIdOrFail($existingColumn, $task);

        $this->taskRepository->delete($existingTask);

        return response()->noContent();
    }
}
