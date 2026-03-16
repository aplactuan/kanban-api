<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreColumnTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StoreColumnTaskController extends Controller
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private ColumnRepositoryInterface $columnRepository,
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(StoreColumnTaskRequest $request, int $board, int $column): JsonResponse
    {
        /** @var array{title: string, description?: string|null, position?: int} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $existingColumn = $this->columnRepository->findForBoardByIdOrFail($existingBoard, $column);
        $task = $this->taskRepository->createForColumn($existingColumn, $validated);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }
}
