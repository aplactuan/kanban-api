<?php

namespace Tests\Feature\Board;

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BoardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_crud_boards(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/v1/boards', [
            'name' => 'Engineering Board',
            'description' => 'Roadmap and sprint planning',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Engineering Board')
            ->assertJsonPath('data.description', 'Roadmap and sprint planning');

        $boardId = $createResponse->json('data.id');
        $this->assertIsInt($boardId);

        $this->getJson('/api/v1/boards')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $boardId);

        $this->getJson('/api/v1/boards/'.$boardId)
            ->assertOk()
            ->assertJsonPath('data.id', $boardId);

        $this->putJson('/api/v1/boards/'.$boardId, [
            'name' => 'Updated Engineering Board',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Engineering Board');

        $this->deleteJson('/api/v1/boards/'.$boardId)
            ->assertNoContent();

        $this->assertDatabaseMissing('boards', [
            'id' => $boardId,
        ]);
    }

    public function test_user_cannot_access_another_users_board(): void
    {
        $authenticatedUser = User::factory()->create();
        $boardOwner = User::factory()->create();
        $otherUsersBoard = Board::factory()->for($boardOwner)->create();

        Sanctum::actingAs($authenticatedUser);

        $this->getJson('/api/v1/boards/'.$otherUsersBoard->id)->assertNotFound();
        $this->putJson('/api/v1/boards/'.$otherUsersBoard->id, ['name' => 'x'])->assertNotFound();
        $this->deleteJson('/api/v1/boards/'.$otherUsersBoard->id)->assertNotFound();
    }

    public function test_store_board_validates_required_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/boards', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
