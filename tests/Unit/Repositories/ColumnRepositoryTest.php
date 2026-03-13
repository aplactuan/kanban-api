<?php

namespace Tests\Unit\Repositories;

use App\Models\Board;
use App\Models\Column;
use App\Repositories\Eloquent\ColumnRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColumnRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_columns_for_the_given_board_in_position_order(): void
    {
        $board = Board::factory()->create();
        $otherBoard = Board::factory()->create();

        Column::factory()->for($board)->create(['name' => 'Done', 'position' => 3]);
        Column::factory()->for($board)->create(['name' => 'To Do', 'position' => 1]);
        Column::factory()->for($otherBoard)->create(['position' => 2]);

        $repository = new ColumnRepository;

        $columns = $repository->getAllForBoard($board);

        $this->assertCount(2, $columns);
        $this->assertSame(['To Do', 'Done'], $columns->pluck('name')->all());
    }

    public function test_it_assigns_the_next_position_when_creating_without_one(): void
    {
        $board = Board::factory()->create();

        Column::factory()->for($board)->create(['position' => 1]);
        Column::factory()->for($board)->create(['position' => 2]);

        $repository = new ColumnRepository;

        $column = $repository->createForBoard($board, [
            'name' => 'Review',
        ]);

        $this->assertSame(3, $column->position);
    }

    public function test_it_throws_when_column_is_not_on_the_given_board(): void
    {
        $board = Board::factory()->create();
        $otherBoard = Board::factory()->create();
        $column = Column::factory()->for($otherBoard)->create();

        $repository = new ColumnRepository;

        $this->expectException(ModelNotFoundException::class);

        $repository->findForBoardByIdOrFail($board, $column->id);
    }
}
