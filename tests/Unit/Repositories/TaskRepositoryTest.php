<?php

namespace Tests\Unit\Repositories;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Repositories\Eloquent\TaskRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_tasks_for_the_given_column_in_position_order(): void
    {
        $column = Column::factory()->create();
        $otherColumn = Column::factory()->create();

        Task::factory()->for($column)->create(['title' => 'Done', 'position' => 3]);
        Task::factory()->for($column)->create(['title' => 'To Do', 'position' => 1]);
        Task::factory()->for($otherColumn)->create(['position' => 2]);

        $repository = new TaskRepository;

        $tasks = $repository->getAllForColumn($column);

        $this->assertCount(2, $tasks);
        $this->assertSame(['To Do', 'Done'], $tasks->pluck('title')->all());
    }

    public function test_it_assigns_the_next_position_when_creating_without_one(): void
    {
        $column = Column::factory()->create();

        Task::factory()->for($column)->create(['position' => 1]);
        Task::factory()->for($column)->create(['position' => 2]);

        $repository = new TaskRepository;

        $task = $repository->createForColumn($column, [
            'title' => 'Review API payload',
        ]);

        $this->assertSame(3, $task->position);
    }

    public function test_it_throws_when_task_is_not_on_the_given_column(): void
    {
        $column = Column::factory()->create();
        $otherColumn = Column::factory()->create();
        $task = Task::factory()->for($otherColumn)->create();

        $repository = new TaskRepository;

        $this->expectException(ModelNotFoundException::class);

        $repository->findForColumnByIdOrFail($column, $task->id);
    }

    public function test_it_can_move_a_task_to_another_column_and_resequence_positions(): void
    {
        $board = Board::factory()->create();
        $sourceColumn = Column::factory()->for($board)->create();
        $targetColumn = Column::factory()->for($board)->create();
        $task = Task::factory()->for($sourceColumn)->create(['position' => 2]);
        $sourceSibling = Task::factory()->for($sourceColumn)->create(['position' => 3]);
        $targetSibling = Task::factory()->for($targetColumn)->create(['position' => 1]);

        $repository = new TaskRepository;

        $movedTask = $repository->move($task, $targetColumn, 1);

        $this->assertSame($targetColumn->id, $movedTask->column_id);
        $this->assertSame(1, $movedTask->position);
        $this->assertSame(2, $targetSibling->refresh()->position);
        $this->assertSame(2, $sourceSibling->refresh()->position);
    }
}
