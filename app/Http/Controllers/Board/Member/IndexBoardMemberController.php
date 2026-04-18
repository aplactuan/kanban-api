<?php

namespace App\Http\Controllers\Board\Member;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\Board;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexBoardMemberController extends Controller
{
    public function __construct(
        private BoardMemberRepositoryInterface $boardMemberRepository
    ) {}

    public function __invoke(Board $board): AnonymousResourceCollection
    {
        $this->authorize('view', $board);

        $members = $this->boardMemberRepository->getMembersForBoard($board);

        return MemberResource::collection($members);
    }
}
