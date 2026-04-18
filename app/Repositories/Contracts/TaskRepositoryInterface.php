<?php

namespace App\Repositories\Contracts;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * @return Collection<int, Task>
     */
    public function getAllForColumn(Column $column): Collection;

    /**
     * @param  array{title: string, description?: string|null, position?: int}  $attributes
     */
    public function createForColumn(Column $column, array $attributes): Task;

    public function findForColumnByIdOrFail(Column $column, int $taskId): Task;

    public function findByIdOrFail(int $taskId): Task;

    /**
     * @param  array{title?: string, description?: string|null, position?: int}  $attributes
     */
    public function update(Task $task, array $attributes): Task;

    public function move(Task $task, Column $targetColumn, int $position): Task;

    public function delete(Task $task): void;
}
