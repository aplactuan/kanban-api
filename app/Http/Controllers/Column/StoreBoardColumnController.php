<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoardColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\Board;
use App\Models\Column;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StoreBoardColumnController extends Controller
{
    public function __construct(
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(StoreBoardColumnRequest $request, Board $board): JsonResponse
    {
        /** @var array{name: string, position?: int} $validated */
        $validated = $request->validated();

        $this->authorize('create', [Column::class, $board]);

        $column = $this->columnRepository->createForBoard($board, $validated);

        return (new ColumnResource($column))->response()->setStatusCode(201);
    }
}
