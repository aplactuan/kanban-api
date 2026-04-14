<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BoardMember extends Pivot
{
    protected $table = 'board_members';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'role',
    ];
}
