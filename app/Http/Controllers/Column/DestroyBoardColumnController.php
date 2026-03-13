<?php

namespace App\Http\Controllers\Column;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DestroyBoardColumnController extends Controller
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private ColumnRepositoryInterface $columnRepository
    ) {}

    public function __invoke(Request $request, int $board, int $column): Response
    {
        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $existingColumn = $this->columnRepository->findForBoardByIdOrFail($existingBoard, $column);

        $this->columnRepository->delete($existingColumn);

        return response()->noContent();
    }
}
