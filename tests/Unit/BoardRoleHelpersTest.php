<?php

namespace Tests\Unit;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\BoardMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BoardRoleHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_role_enum_values_match_database_role_column(): void
    {
        $this->assertSame('owner', BoardRole::Owner->value);
        $this->assertSame('admin', BoardRole::Admin->value);
        $this->assertSame('member', BoardRole::Member->value);

        $this->assertEqualsCanonicalizing(
            ['owner', 'admin', 'member'],
            array_map(fn (BoardRole $r) => $r->value, BoardRole::cases())
        );
    }

    public function test_hierarchy_level_orders_roles(): void
    {
        $this->assertGreaterThan(BoardRole::Admin->rank(), BoardRole::Owner->rank());
        $this->assertGreaterThan(BoardRole::Member->rank(), BoardRole::Admin->rank());
    }

    #[DataProvider('userIsAtLeastMatrixProvider')]
    public function test_user_is_at_least_for_each_role_combination(
        BoardRole $actualRole,
        BoardRole $minimumRole,
        bool $expected,
    ): void {
        $user = User::factory()->create();
        $boardOwner = User::factory()->create();
        $board = Board::factory()->for($boardOwner)->create();
        $board->members()->attach($user->id, ['role' => $actualRole->value]);

        $board = $board->fresh();

        $this->assertSame($expected, $board->userIsAtLeast($user, $minimumRole));
    }

    /**
     * @return iterable<string, array{BoardRole, BoardRole, bool}>
     */
    public static function userIsAtLeastMatrixProvider(): iterable
    {
        yield 'owner meets owner' => [BoardRole::Owner, BoardRole::Owner, true];
        yield 'owner meets admin' => [BoardRole::Owner, BoardRole::Admin, true];
        yield 'owner meets member' => [BoardRole::Owner, BoardRole::Member, true];

        yield 'admin meets owner' => [BoardRole::Admin, BoardRole::Owner, false];
        yield 'admin meets admin' => [BoardRole::Admin, BoardRole::Admin, true];
        yield 'admin meets member' => [BoardRole::Admin, BoardRole::Member, true];

        yield 'member meets owner' => [BoardRole::Member, BoardRole::Owner, false];
        yield 'member meets admin' => [BoardRole::Member, BoardRole::Admin, false];
        yield 'member meets member' => [BoardRole::Member, BoardRole::Member, true];
    }

    public function test_get_user_role_returns_null_when_not_a_member(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $board = Board::factory()->for($other)->create();

        $board = $board->fresh();

        $this->assertNull($board->getUserRole($user));
        $this->assertFalse($board->userIsMember($user));
    }

    public function test_get_user_role_and_user_is_member_for_member(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($user->id, ['role' => BoardRole::Admin->value]);

        $board = $board->fresh();

        $this->assertSame(BoardRole::Admin, $board->getUserRole($user));
        $this->assertTrue($board->userIsMember($user));
    }

    public function test_board_member_pivot_is_at_least_uses_same_hierarchy(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($user->id, ['role' => BoardRole::Member->value]);

        $board = $board->fresh();
        /** @var BoardMember $pivot */
        $pivot = $board->members()->whereKey($user->id)->first()->pivot;

        $this->assertTrue($pivot->isAtLeast(BoardRole::Member));
        $this->assertFalse($pivot->isAtLeast(BoardRole::Admin));
    }
}
