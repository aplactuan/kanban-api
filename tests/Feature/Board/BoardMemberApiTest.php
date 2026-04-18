<?php

namespace Tests\Feature\Board;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BoardMemberApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_list_board_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($member);

        $response = $this->getJson('/api/v1/boards/'.$board->id.'/members');

        $response->assertOk()->assertJsonCount(2, 'data');

        $rows = collect($response->json('data'));
        $this->assertTrue($rows->contains(fn (array $row): bool => $row['user_id'] === $owner->id && $row['role'] === 'owner'));
        $this->assertTrue($rows->contains(fn (array $row): bool => $row['user_id'] === $member->id && $row['role'] === 'member'));
    }

    public function test_non_member_cannot_list_board_members(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $board = Board::factory()->for($owner)->create();

        Sanctum::actingAs($outsider);

        $this->getJson('/api/v1/boards/'.$board->id.'/members')->assertForbidden();
    }

    public function test_owner_can_invite_existing_user_by_email(): void
    {
        $owner = User::factory()->create();
        $invite = User::factory()->create(['email' => 'invitee@example.com']);
        $board = Board::factory()->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/boards/'.$board->id.'/members', [
            'email' => 'invitee@example.com',
        ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $invite->id)
            ->assertJsonPath('data.role', 'member');

        $this->assertDatabaseHas('board_members', [
            'board_id' => $board->id,
            'user_id' => $invite->id,
            'role' => 'member',
        ]);
    }

    public function test_invite_with_unknown_email_returns_validation_error(): void
    {
        $owner = User::factory()->create();
        $board = Board::factory()->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/boards/'.$board->id.'/members', [
            'email' => 'nobody@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_invite_duplicate_member_returns_validation_error(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['email' => 'already@example.com']);
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/boards/'.$board->id.'/members', [
            'email' => 'already@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_member_cannot_invite_users(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $other = User::factory()->create(['email' => 'other@example.com']);
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($member);

        $this->postJson('/api/v1/boards/'.$board->id.'/members', [
            'email' => $other->email,
        ])->assertForbidden();
    }

    public function test_owner_can_promote_member_to_admin(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($owner);

        $this->putJson('/api/v1/boards/'.$board->id.'/members/'.$member->id, [
            'role' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_admin_cannot_promote_member_to_admin(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($admin->id, ['role' => BoardRole::Admin->value, 'created_at' => now()]);
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($admin);

        $this->putJson('/api/v1/boards/'.$board->id.'/members/'.$member->id, [
            'role' => 'admin',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_cannot_change_owner_role_via_put(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($admin->id, ['role' => BoardRole::Admin->value, 'created_at' => now()]);

        Sanctum::actingAs($admin);

        $this->putJson('/api/v1/boards/'.$board->id.'/members/'.$owner->id, [
            'role' => 'member',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_owner_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($owner);

        $this->deleteJson('/api/v1/boards/'.$board->id.'/members/'.$member->id)->assertNoContent();

        $this->assertDatabaseMissing('board_members', [
            'board_id' => $board->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_admin_cannot_remove_another_admin(): void
    {
        $owner = User::factory()->create();
        $adminA = User::factory()->create();
        $adminB = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($adminA->id, ['role' => BoardRole::Admin->value, 'created_at' => now()]);
        $board->members()->attach($adminB->id, ['role' => BoardRole::Admin->value, 'created_at' => now()]);

        Sanctum::actingAs($adminA);

        $this->deleteJson('/api/v1/boards/'.$board->id.'/members/'.$adminB->id)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user']);
    }

    public function test_member_cannot_remove_others(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $other = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);
        $board->members()->attach($other->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($member);

        $this->deleteJson('/api/v1/boards/'.$board->id.'/members/'.$other->id)->assertForbidden();
    }

    public function test_member_can_leave_board(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($member);

        $this->deleteJson('/api/v1/boards/'.$board->id.'/members/leave')->assertNoContent();

        $this->assertDatabaseMissing('board_members', [
            'board_id' => $board->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_cannot_leave_without_transfer(): void
    {
        $owner = User::factory()->create();
        $board = Board::factory()->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->deleteJson('/api/v1/boards/'.$board->id.'/members/leave')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['board']);
    }

    public function test_owner_can_transfer_ownership(): void
    {
        $owner = User::factory()->create();
        $successor = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($successor->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($owner);

        $this->patchJson('/api/v1/boards/'.$board->id.'/members/'.$successor->id.'/transfer-ownership')
            ->assertOk()
            ->assertJsonPath('data.user_id', $successor->id)
            ->assertJsonPath('data.role', 'owner');

        $board->refresh();

        $this->assertSame($successor->id, $board->user_id);
        $this->assertDatabaseHas('board_members', [
            'board_id' => $board->id,
            'user_id' => $owner->id,
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('board_members', [
            'board_id' => $board->id,
            'user_id' => $successor->id,
            'role' => 'owner',
        ]);
    }

    public function test_admin_cannot_transfer_ownership(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($admin->id, ['role' => BoardRole::Admin->value, 'created_at' => now()]);
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value, 'created_at' => now()]);

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/boards/'.$board->id.'/members/'.$member->id.'/transfer-ownership')
            ->assertForbidden();
    }

    public function test_invite_endpoint_is_throttled_after_ten_requests_per_minute(): void
    {
        $owner = User::factory()->create();
        $board = Board::factory()->for($owner)->create();

        $invitees = User::factory()->count(11)->create();

        Sanctum::actingAs($owner);

        foreach ($invitees->take(10) as $user) {
            $this->postJson('/api/v1/boards/'.$board->id.'/members', [
                'email' => $user->email,
            ])->assertCreated();
        }

        $this->postJson('/api/v1/boards/'.$board->id.'/members', [
            'email' => $invitees->last()->email,
        ])->assertStatus(429);
    }
}
