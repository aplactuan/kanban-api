<?php

namespace App\Repositories\Contracts;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\BoardMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface BoardMemberRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function getMembersForBoard(Board $board): Collection;

    public function findMemberOrFail(Board $board, User $user): BoardMember;

    public function inviteByEmail(Board $board, string $email, BoardRole $role): User;

    public function updateMemberRole(Board $board, User $actor, User $subject, BoardRole $newRole): void;

    public function removeMember(Board $board, User $actor, User $subject): void;

    public function leave(Board $board, User $user): void;

    public function transferOwnership(Board $board, User $actor, User $newOwner): void;
}
