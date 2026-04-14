<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('boards')
            ->select(['id', 'user_id'])
            ->orderBy('id')
            ->chunkById(500, function ($boards): void {
                $now = now();
                $rows = [];

                foreach ($boards as $board) {
                    $rows[] = [
                        'board_id' => $board->id,
                        'user_id' => $board->user_id,
                        'role' => 'owner',
                        'created_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('board_members')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('board_members')
            ->where('role', 'owner')
            ->delete();
    }
};
