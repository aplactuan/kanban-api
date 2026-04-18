<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Concerns\ParsesIncludes;
use App\Http\Controllers\Controller;
use App\Http\Resources\ColumnResource;
use App\Models\Board;
use App\Models\Column;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexBoardColumnController extends Controller
{
    use ParsesIncludes;

    public function __construct(
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(Request $request, Board $board): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Column::class, $board]);

        $columns = $this->columnRepository->getAllForBoard($board);

        $relations = $this->parseIncludes($request, ['tasks']);

        if ($relations !== []) {
            $columns->load($relations);
        }

        return ColumnResource::collection($columns);
    }
}
