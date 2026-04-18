<?php

namespace Database\Factories;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Board>
 */
class BoardFactory extends Factory
{
    protected $model = Board::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Board $board): void {
            if ($board->user_id === null) {
                return;
            }

            if ($board->members()->whereKey($board->user_id)->exists()) {
                return;
            }

            $board->members()->attach($board->user_id, ['role' => BoardRole::Owner->value]);
        });
    }
}
