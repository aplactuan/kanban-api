<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateColumnTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;

class UpdateColumnTaskController extends Controller
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private ColumnRepositoryInterface $columnRepository,
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(UpdateColumnTaskRequest $request, int $board, int $column, int $task): TaskResource
    {
        /** @var array{title?: string, description?: string|null, position?: int} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $existingColumn = $this->columnRepository->findForBoardByIdOrFail($existingBoard, $column);
        $existingTask = $this->taskRepository->findForColumnByIdOrFail($existingColumn, $task);
        $updatedTask = $this->taskRepository->update($existingTask, $validated);

        return new TaskResource($updatedTask);
    }
}
