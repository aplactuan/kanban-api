<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\User;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;

class MoveTaskController extends Controller
{
    public function __construct(
        private ColumnRepositoryInterface $columnRepository,
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(MoveTaskRequest $request, int $task): TaskResource
    {
        /** @var array{column_id: int, position: int} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $existingTask = $this->taskRepository->findForUserByIdOrFail($user, $task);
        $targetColumn = $this->columnRepository->findForBoardByIdOrFail($existingTask->column->board, $validated['column_id']);

        $movedTask = $this->taskRepository->move($existingTask, $targetColumn, $validated['position']);

        return new TaskResource($movedTask);
    }
}
