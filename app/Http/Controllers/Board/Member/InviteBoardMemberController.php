<?php

namespace App\Http\Controllers\Board\Member;

use App\Enums\BoardRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Board\Member\InviteMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Board;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;
use Illuminate\Http\JsonResponse;

class InviteBoardMemberController extends Controller
{
    public function __construct(
        private BoardMemberRepositoryInterface $boardMemberRepository
    ) {}

    public function __invoke(InviteMemberRequest $request, Board $board): JsonResponse
    {
        $this->authorize('manageMembers', $board);

        /** @var array{email: string, role?: string} $validated */
        $validated = $request->validated();

        $role = isset($validated['role'])
            ? BoardRole::from($validated['role'])
            : BoardRole::Member;

        $invitedUser = $this->boardMemberRepository->inviteByEmail(
            $board,
            $validated['email'],
            $role
        );

        $member = $board->members()->whereKey($invitedUser->id)->firstOrFail();

        return (new MemberResource($member))->response()->setStatusCode(201);
    }
}
