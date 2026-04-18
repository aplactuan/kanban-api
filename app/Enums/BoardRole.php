<?php

namespace App\Enums;

enum BoardRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function rank(): int
    {
        return match ($this) {
            self::Member => 1,
            self::Admin => 2,
            self::Owner => 3,
        };
    }

    public function isAtLeast(self $minimum): bool
    {
        return $this->rank() >= $minimum->rank();
    }
}
