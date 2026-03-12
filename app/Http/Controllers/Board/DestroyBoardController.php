<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DestroyBoardController extends Controller
{
    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function __invoke(Request $request, int $board): Response
    {
        /** @var User $user */
        $user = $request->user();

        $existingBoard = $this->boardRepository->findForUserByIdOrFail($user, $board);
        $this->boardRepository->delete($existingBoard);

        return response()->noContent();
    }
}
