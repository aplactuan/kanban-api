<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DestroyBoardColumnController extends Controller
{
    public function __construct(
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(Request $request, Board $board, Column $column): Response
    {
        $this->authorize('delete', $column);

        $this->columnRepository->delete($column);

        return response()->noContent();
    }
}
