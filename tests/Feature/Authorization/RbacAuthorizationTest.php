<?php

namespace Tests\Feature\Authorization;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_update_or_delete_board_but_can_view_it(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value]);

        Sanctum::actingAs($member);

        $this->getJson('/api/v1/boards/'.$board->id)->assertOk();
        $this->putJson('/api/v1/boards/'.$board->id, ['name' => 'Renamed'])->assertForbidden();
        $this->deleteJson('/api/v1/boards/'.$board->id)->assertForbidden();
    }

    public function test_admin_can_update_board_but_cannot_delete_it(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($admin->id, ['role' => BoardRole::Admin->value]);

        Sanctum::actingAs($admin);

        $this->putJson('/api/v1/boards/'.$board->id, ['name' => 'Admin rename'])->assertOk();
        $this->deleteJson('/api/v1/boards/'.$board->id)->assertForbidden();
    }

    public function test_owner_can_delete_board(): void
    {
        $owner = User::factory()->create();
        $board = Board::factory()->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->deleteJson('/api/v1/boards/'.$board->id)->assertNoContent();
    }

    public function test_member_cannot_create_or_update_columns_but_admin_can(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $admin = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value]);
        $board->members()->attach($admin->id, ['role' => BoardRole::Admin->value]);
        $column = Column::factory()->for($board)->create();

        Sanctum::actingAs($member);

        $this->postJson('/api/v1/boards/'.$board->id.'/columns', ['name' => 'New'])->assertForbidden();
        $this->putJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id, ['name' => 'Renamed'])->assertForbidden();

        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/boards/'.$board->id.'/columns', ['name' => 'Admin column'])
            ->assertCreated();
        $this->putJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id, ['name' => 'Admin edit'])
            ->assertOk();
    }

    public function test_member_can_create_and_update_tasks(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value]);
        $column = Column::factory()->for($board)->create();

        Sanctum::actingAs($member);

        $create = $this->postJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id.'/tasks', [
            'title' => 'Member task',
        ]);

        $create->assertCreated();
        $taskId = $create->json('data.id');
        $this->assertIsInt($taskId);

        $this->putJson('/api/v1/boards/'.$board->id.'/columns/'.$column->id.'/tasks/'.$taskId, [
            'title' => 'Updated by member',
        ])->assertOk();
    }

    public function test_member_cannot_move_task_to_a_column_on_another_board(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->for($owner)->create();
        $board->members()->attach($member->id, ['role' => BoardRole::Member->value]);
        $sourceColumn = Column::factory()->for($board)->create();
        $task = Task::factory()->for($sourceColumn)->create();

        $otherBoard = Board::factory()->for($owner)->create();
        $foreignColumn = Column::factory()->for($otherBoard)->create();

        Sanctum::actingAs($member);

        $this->patchJson('/api/v1/tasks/'.$task->id.'/move', [
            'column_id' => $foreignColumn->id,
            'position' => 1,
        ])->assertForbidden();
    }
}
