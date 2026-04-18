<?php

namespace Tests\Unit\Repositories;

use App\Models\Board;
use App\Models\User;
use App\Repositories\Eloquent\BoardRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_attaches_owner_membership_when_creating_board(): void
    {
        $user = User::factory()->create();
        $repository = new BoardRepository;

        $board = $repository->createForUser($user, [
            'name' => 'Planning',
            'description' => 'Team planning board',
        ]);

        $this->assertDatabaseHas('board_members', [
            'board_id' => $board->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_it_returns_only_boards_for_the_given_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Board::factory()->count(2)->for($user)->create();
        Board::factory()->count(3)->for($otherUser)->create();

        $repository = new BoardRepository;

        $boards = $repository->getAllForUser($user);

        $this->assertCount(2, $boards);
        $this->assertTrue($boards->every(fn (Board $board) => $board->user_id === $user->id));
    }

    public function test_find_by_id_or_fail_returns_the_board_when_it_exists(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();

        $repository = new BoardRepository;

        $found = $repository->findByIdOrFail($board->id);

        $this->assertTrue($found->is($board));
    }
}
