<?php

namespace App\Policies;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Board $board): bool
    {
        return $board->userIsAtLeast($user, BoardRole::Member);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Board $board): bool
    {
        return $board->userIsAtLeast($user, BoardRole::Admin);
    }

    public function delete(User $user, Board $board): bool
    {
        return $board->getUserRole($user) === BoardRole::Owner;
    }

    public function manageMembers(User $user, Board $board): bool
    {
        return $board->userIsAtLeast($user, BoardRole::Admin);
    }

    public function transferOwnership(User $user, Board $board, User $target): bool
    {
        return $board->getUserRole($user) === BoardRole::Owner
            && $board->userIsMember($target)
            && $user->id !== $target->id;
    }
}
