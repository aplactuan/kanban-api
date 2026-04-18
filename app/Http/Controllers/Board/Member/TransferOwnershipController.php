<?php

namespace App\Http\Controllers\Board\Member;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\Board;
use App\Models\User;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;
use Illuminate\Http\Request;

class TransferOwnershipController extends Controller
{
    public function __construct(
        private BoardMemberRepositoryInterface $boardMemberRepository
    ) {}

    public function __invoke(Request $request, Board $board, User $member): MemberResource
    {
        /** @var User $actor */
        $actor = $request->user();

        $this->authorize('transferOwnership', [$board, $member]);

        $this->boardMemberRepository->transferOwnership($board, $actor, $member);

        $board->refresh();

        $memberRow = $board->members()->whereKey($member->id)->firstOrFail();

        return new MemberResource($memberRow);
    }
}
