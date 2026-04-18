<?php

namespace Tests\Feature\Task;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_crud_tasks_within_a_column(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();
        $column = Column::factory()->for($board)->create();

        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id.'/tasks', [
            'title' => 'Write API tests',
            'description' => 'Cover task CRUD endpoints',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.column_id', $column->id)
            ->assertJsonPath('data.title', 'Write API tests')
            ->assertJsonPath('data.description', 'Cover task CRUD endpoints')
            ->assertJsonPath('data.position', 1);

        $taskId = $createResponse->json('data.id');
        $this->assertIsInt($taskId);

        $this->getJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id.'/tasks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $taskId);

        $this->putJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id.'/tasks/'.$taskId, [
            'title' => 'Ship API tests',
            'position' => 2,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Ship API tests')
            ->assertJsonPath('data.position', 2);

        $this->deleteJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id.'/tasks/'.$taskId)
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', [
            'id' => $taskId,
        ]);
    }

    public function test_user_cannot_access_tasks_for_another_users_board(): void
    {
        $authenticatedUser = User::factory()->create();
        $boardOwner = User::factory()->create();
        $otherUsersBoard = Board::factory()->for($boardOwner)->create();
        $otherUsersColumn = Column::factory()->for($otherUsersBoard)->create();
        $otherUsersTask = Task::factory()->for($otherUsersColumn)->create();

        Sanctum::actingAs($authenticatedUser);

        $this->getJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns/'.$otherUsersColumn->id.'/tasks')->assertForbidden();
        $this->postJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns/'.$otherUsersColumn->id.'/tasks', ['title' => 'Blocked'])->assertForbidden();
        $this->putJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns/'.$otherUsersColumn->id.'/tasks/'.$otherUsersTask->id, ['title' => 'Blocked'])->assertForbidden();
        $this->deleteJson('/api/v1/boards/'.$otherUsersBoard->id.'/columns/'.$otherUsersColumn->id.'/tasks/'.$otherUsersTask->id)->assertForbidden();
        $this->patchJson('/api/v1/tasks/'.$otherUsersTask->id.'/move', ['column_id' => $otherUsersColumn->id, 'position' => 1])->assertForbidden();
    }

    public function test_user_cannot_modify_a_task_through_the_wrong_column_route(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();
        $firstColumn = Column::factory()->for($board)->create();
        $secondColumn = Column::factory()->for($board)->create();
        $task = Task::factory()->for($secondColumn)->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/boards/'.$board->id.'/columns/'.$firstColumn->id.'/tasks/'.$task->id, [
            'title' => 'Wrong Column',
        ])->assertNotFound();

        $this->deleteJson('/api/v1/boards/'.$board->id.'/columns/'.$firstColumn->id.'/tasks/'.$task->id)
            ->assertNotFound();
    }

    public function test_user_cannot_modify_a_task_through_the_wrong_board_route(): void
    {
        $user = User::factory()->create();
        $firstBoard = Board::factory()->for($user)->create();
        $secondBoard = Board::factory()->for($user)->create();
        $column = Column::factory()->for($secondBoard)->create();
        $task = Task::factory()->for($column)->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/boards/'.$firstBoard->id.'/columns/'.$column->id.'/tasks/'.$task->id, [
            'title' => 'Wrong Board',
        ])->assertNotFound();

        $this->deleteJson('/api/v1/boards/'.$firstBoard->id.'/columns/'.$column->id.'/tasks/'.$task->id)
            ->assertNotFound();
    }

    public function test_store_task_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();
        $column = Column::factory()->for($board)->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id.'/tasks', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_user_can_move_a_task_to_another_column(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();
        $sourceColumn = Column::factory()->for($board)->create();
        $targetColumn = Column::factory()->for($board)->create();
        $task = Task::factory()->for($sourceColumn)->create(['position' => 1]);
        $existingTargetTask = Task::factory()->for($targetColumn)->create(['position' => 1]);

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/'.$task->id.'/move', [
            'column_id' => $targetColumn->id,
            'position' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.column_id', $targetColumn->id)
            ->assertJsonPath('data.position', 1);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'column_id' => $targetColumn->id,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $existingTargetTask->id,
            'position' => 2,
        ]);
    }

    public function test_user_can_reorder_a_task_within_the_same_column(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->for($user)->create();
        $column = Column::factory()->for($board)->create();
        $firstTask = Task::factory()->for($column)->create(['position' => 1]);
        $secondTask = Task::factory()->for($column)->create(['position' => 2]);
        $thirdTask = Task::factory()->for($column)->create(['position' => 3]);

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/tasks/'.$thirdTask->id.'/move', [
            'column_id' => $column->id,
            'position' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $thirdTask->id)
            ->assertJsonPath('data.position', 1);

        $this->assertDatabaseHas('tasks', [
            'id' => $thirdTask->id,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $firstTask->id,
            'position' => 2,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $secondTask->id,
            'position' => 3,
        ]);
    }
}
