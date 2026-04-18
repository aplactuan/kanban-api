<?php

namespace App\Http\Controllers\Board\Member;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LeaveBoardController extends Controller
{
    public function __construct(
        private BoardMemberRepositoryInterface $boardMemberRepository
    ) {}

    public function __invoke(Request $request, Board $board): Response
    {
        $this->authorize('view', $board);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->boardMemberRepository->leave($board, $user);

        return response()->noContent();
    }
}
