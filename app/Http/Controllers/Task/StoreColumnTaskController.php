<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreColumnTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StoreColumnTaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(StoreColumnTaskRequest $request, Board $board, Column $column): JsonResponse
    {
        /** @var array{title: string, description?: string|null, position?: int} $validated */
        $validated = $request->validated();

        $this->authorize('create', [Task::class, $column]);

        $task = $this->taskRepository->createForColumn($column, $validated);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }
}
