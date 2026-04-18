<?php

namespace App\Models;

use App\Enums\BoardRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    /** @use HasFactory<\Database\Factories\BoardFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_members')
            ->using(BoardMember::class)
            ->withPivot('role');
    }

    public function getUserRole(User $user): ?BoardRole
    {
        $member = $this->members()->whereKey($user->id)->first();

        if ($member === null) {
            return null;
        }

        $role = $member->pivot->role;

        return $role instanceof BoardRole ? $role : BoardRole::tryFrom((string) $role);
    }

    public function userIsAtLeast(User $user, BoardRole $role): bool
    {
        return $this->getUserRole($user)?->isAtLeast($role) ?? false;
    }

    public function userIsMember(User $user): bool
    {
        return $this->getUserRole($user) !== null;
    }
}
