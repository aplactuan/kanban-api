<?php

namespace App\Models;

use App\Enums\BoardRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BoardMember extends Pivot
{
    protected $table = 'board_members';

    public $timestamps = false;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'role',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => BoardRole::class,
        ];
    }

    public function isAtLeast(BoardRole $minimum): bool
    {
        $actual = $this->role instanceof BoardRole
            ? $this->role
            : BoardRole::tryFrom((string) $this->getRawOriginal('role'));

        return $actual?->isAtLeast($minimum) ?? false;
    }
}
