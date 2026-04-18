<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DestroyColumnTaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function __invoke(Request $request, Board $board, Column $column, Task $task): Response
    {
        $this->authorize('delete', $task);

        $this->taskRepository->delete($task);

        return response()->noContent();
    }
}
