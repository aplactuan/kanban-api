<?php

namespace App\Repositories\Eloquent;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\BoardMember;
use App\Models\User;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BoardMemberRepository implements BoardMemberRepositoryInterface
{
    public function getMembersForBoard(Board $board): Collection
    {
        return $board->members()
            ->orderBy('name')
            ->get();
    }

    public function findMemberOrFail(Board $board, User $user): BoardMember
    {
        return BoardMember::query()
            ->where('board_id', $board->id)
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    public function inviteByEmail(Board $board, string $email, BoardRole $role): User
    {
        if ($role === BoardRole::Owner) {
            throw ValidationException::withMessages([
                'role' => ['New members cannot be invited as owner. Use ownership transfer instead.'],
            ]);
        }

        $inviteUser = User::query()->where('email', $email)->first();

        if ($inviteUser === null) {
            throw ValidationException::withMessages([
                'email' => ['We could not add this member. Check the email address and try again.'],
            ]);
        }

        if ($board->members()->whereKey($inviteUser->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This user is already a member of this board.'],
            ]);
        }

        $board->members()->attach($inviteUser->id, [
            'role' => $role->value,
            'created_at' => now(),
        ]);

        return $inviteUser;
    }

    public function updateMemberRole(Board $board, User $actor, User $subject, BoardRole $newRole): void
    {
        if ($newRole === BoardRole::Owner) {
            throw ValidationException::withMessages([
                'role' => ['Promoting a user to owner is not allowed here. Use ownership transfer instead.'],
            ]);
        }

        if (! $board->userIsMember($subject)) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of this board.'],
            ]);
        }

        $subjectRole = $board->getUserRole($subject);

        if ($subjectRole === BoardRole::Owner || $subject->id === $board->user_id) {
            throw ValidationException::withMessages([
                'role' => ['The board owner role cannot be changed with this action.'],
            ]);
        }

        $actorRole = $board->getUserRole($actor);

        if ($actorRole === BoardRole::Admin) {
            if ($newRole === BoardRole::Admin) {
                throw ValidationException::withMessages([
                    'role' => ['Only the board owner can assign the admin role.'],
                ]);
            }

            if ($subjectRole === BoardRole::Admin) {
                throw ValidationException::withMessages([
                    'role' => ['Admins cannot change another admin member.'],
                ]);
            }
        }

        DB::table('board_members')
            ->where('board_id', $board->id)
            ->where('user_id', $subject->id)
            ->update(['role' => $newRole->value]);
    }

    public function removeMember(Board $board, User $actor, User $subject): void
    {
        if (! $board->userIsMember($subject)) {
            throw ValidationException::withMessages([
                'user' => ['This user is not a member of this board.'],
            ]);
        }

        $subjectRole = $board->getUserRole($subject);

        if ($subjectRole === BoardRole::Owner || $subject->id === $board->user_id) {
            throw ValidationException::withMessages([
                'user' => ['The board owner cannot be removed from the board.'],
            ]);
        }

        $actorRole = $board->getUserRole($actor);

        if ($actorRole === BoardRole::Admin && $subjectRole === BoardRole::Admin) {
            throw ValidationException::withMessages([
                'user' => ['Admins cannot remove another admin from the board.'],
            ]);
        }

        $board->members()->detach($subject->id);
    }

    public function leave(Board $board, User $user): void
    {
        if ($board->user_id === $user->id) {
            throw ValidationException::withMessages([
                'board' => ['The board owner must transfer ownership before leaving the board.'],
            ]);
        }

        if (! $board->userIsMember($user)) {
            throw ValidationException::withMessages([
                'board' => ['You are not a member of this board.'],
            ]);
        }

        $board->members()->detach($user->id);
    }

    public function transferOwnership(Board $board, User $actor, User $newOwner): void
    {
        if ($board->getUserRole($actor) !== BoardRole::Owner) {
            throw ValidationException::withMessages([
                'board' => ['Only the board owner can transfer ownership.'],
            ]);
        }

        if ($actor->id === $newOwner->id) {
            throw ValidationException::withMessages([
                'user' => ['Choose a different member to receive ownership.'],
            ]);
        }

        if (! $board->userIsMember($newOwner)) {
            throw ValidationException::withMessages([
                'user' => ['The new owner must already be a member of this board.'],
            ]);
        }

        $currentOwnerId = $board->user_id;

        DB::transaction(function () use ($board, $currentOwnerId, $newOwner): void {
            DB::table('board_members')
                ->where('board_id', $board->id)
                ->where('user_id', $currentOwnerId)
                ->update(['role' => BoardRole::Admin->value]);

            DB::table('board_members')
                ->where('board_id', $board->id)
                ->where('user_id', $newOwner->id)
                ->update(['role' => BoardRole::Owner->value]);

            $board->forceFill(['user_id' => $newOwner->id])->save();
        });
    }
}
