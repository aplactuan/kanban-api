<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Column;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class MoveTaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(MoveTaskRequest $request, Task $task): TaskResource
    {
        /** @var array{column_id: int, position: int} $validated */
        $validated = $request->validated();

        $task->loadMissing('column.board');

        $this->authorize('update', $task);

        $targetColumn = Column::query()->findOrFail($validated['column_id']);

        $this->authorize('move', [$task, $targetColumn]);

        $movedTask = $this->taskRepository->move($task, $targetColumn, $validated['position']);

        return new TaskResource($movedTask);
    }
}
