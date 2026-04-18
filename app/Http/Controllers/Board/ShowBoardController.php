<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Concerns\ParsesIncludes;
use App\Http\Controllers\Controller;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Http\Request;

class ShowBoardController extends Controller
{
    use ParsesIncludes;

    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(Request $request, Board $board): BoardResource
    {
        $this->authorize('view', $board);

        $relations = $this->parseIncludes($request, ['columns', 'columns.tasks']);

        if ($relations !== []) {
            $board->loadMissing($relations);
        }

        return new BoardResource($board);
    }
}
