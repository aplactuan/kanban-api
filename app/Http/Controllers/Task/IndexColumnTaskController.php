<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexColumnTaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(Request $request, Board $board, Column $column): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Task::class, $column]);

        return TaskResource::collection($this->taskRepository->getAllForColumn($column));
    }
}
