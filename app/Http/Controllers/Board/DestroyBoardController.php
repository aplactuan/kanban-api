<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DestroyBoardController extends Controller
{
    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(Request $request, Board $board): Response
    {
        $this->authorize('delete', $board);

        $this->boardRepository->delete($board);

        return response()->noContent();
    }
}
