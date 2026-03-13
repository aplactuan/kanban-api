<?php

namespace Tests\Feature\Column;

use App\Models\Board;
use App\Models\Column;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ColumnApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_crud_columns_within_a_board(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/v1/boards/'.$board->id.'/columns', [
            'name' => 'To Do',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.board_id', $board->id)
            ->assertJsonPath('data.name', 'To Do')
            ->assertJsonPath('data.position', 1);

        $columnId = $createResponse->json('data.id');
        $this->assertIsInt($columnId);

        $this->getJson('/api/v1/boards/'.$board->id.'/columns')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $columnId);

        $this->putJson('/api/v1/boards/'.$board->id.'/columns/'.$columnId, [
            'name' => 'In Progress',
            'position' => 2,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'In Progress')
            ->assertJsonPath('data.position', 2);

        $this->deleteJson('/api/v1/boards/'.$board->id.'/columns/'.$columnId)
            ->assertNoContent();

        $this->assertDatabaseMissing('columns', [
            'id' => $columnId,
        ]);
    }

    public function test_user_cannot_access_columns_for_another_users_board(): void
    {
        $authenticatedUser = User::factory()->create();
        $boardOwner = User::factory()->create();
        $otherUsersBoard = Board::factory()->for($boardOwner)->create();
        $otherUsersColumn = Column::factory()->for($otherUsersBoard)->create();

        Sanctum::actingAs($authenticatedUser);

        $this->getJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns')->assertNotFound();
        $this->postJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns', ['name' => 'Blocked'])->assertNotFound();
        $this->putJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns/'.$otherUsersColumn->id, ['name' => 'Blocked'])->assertNotFound();
        $this->deleteJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns/'.$otherUsersColumn->id)->assertNotFound();
    }

    public function test_user_cannot_modify_a_column_through_the_wrong_board_route(): void
    {
        $user = User::factory()->create();
        $firstBoard = Board::factory()->for($user)->create();
        $secondBoard = Board::factory()->for($user)->create();
        $column = Column::factory()->for($secondBoard)->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/boards/'.$firstBoard->id.'/columns/'.$column->id, [
            'name' => 'Wrong Board',
        ])->assertNotFound();

        $this->deleteJson('/api/v1/boards/'.$firstBoard->id.'/columns/'.$column->id)
            ->assertNotFound();
    }

    public function test_store_column_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/boards/'.$board->id.'/columns', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
