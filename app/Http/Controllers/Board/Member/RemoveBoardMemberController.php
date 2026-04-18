<?php

namespace App\Http\Controllers\Board\Member;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\User;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RemoveBoardMemberController extends Controller
{
    public function __construct(
        private BoardMemberRepositoryInterface $boardMemberRepository
    ) {}

    public function __invoke(Request $request, Board $board, User $member): Response
    {
        $this->authorize('manageMembers', $board);

        /** @var User $actor */
        $actor = $request->user();

        $this->boardMemberRepository->removeMember($board, $actor, $member);

        return response()->noContent();
    }
}
