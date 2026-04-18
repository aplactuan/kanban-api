<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateColumnTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class UpdateColumnTaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(UpdateColumnTaskRequest $request, Board $board, Column $column, Task $task): TaskResource
    {
        /** @var array{title?: string, description?: string|null, position?: int} $validated */
        $validated = $request->validated();

        $this->authorize('update', $task);

        $updatedTask = $this->taskRepository->update($task, $validated);

        return new TaskResource($updatedTask);
    }
}
