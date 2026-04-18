<?php

namespace App\Http\Controllers\Board\Member;

use App\Enums\BoardRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Board\Member\UpdateMemberRoleRequest;
use App\Http\Resources\MemberResource;
use App\Models\Board;
use App\Models\User;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;

class UpdateBoardMemberController extends Controller
{
    public function __construct(
        private BoardMemberRepositoryInterface $boardMemberRepository
    ) {}

    public function __invoke(UpdateMemberRoleRequest $request, Board $board, User $member): MemberResource
    {
        $this->authorize('manageMembers', $board);

        /** @var array{role: string} $validated */
        $validated = $request->validated();

        /** @var User $actor */
        $actor = $request->user();

        $this->boardMemberRepository->updateMemberRole(
            $board,
            $actor,
            $member,
            BoardRole::from($validated['role'])
        );

        $memberRow = $board->members()->whereKey($member->id)->firstOrFail();

        return new MemberResource($memberRow);
    }
}
