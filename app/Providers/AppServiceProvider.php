<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Policies\BoardPolicy;
use App\Policies\ColumnPolicy;
use App\Policies\TaskPolicy;
use App\Repositories\Contracts\BoardMemberRepositoryInterface;
use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Eloquent\BoardMemberRepository;
use App\Repositories\Eloquent\BoardRepository;
use App\Repositories\Eloquent\ColumnRepository;
use App\Repositories\Eloquent\TaskRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BoardRepositoryInterface::class, BoardRepository::class);
        $this->app->bind(ColumnRepositoryInterface::class, ColumnRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(BoardMemberRepositoryInterface::class, BoardMemberRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Board::class, BoardPolicy::class);
        Gate::policy(Column::class, ColumnPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);

        RateLimiter::for('board-member-invite', function (Request $request): Limit {
            $key = (string) ($request->user()?->getAuthIdentifier() ?? $request->ip());

            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('board-member-remove', function (Request $request): Limit {
            $key = (string) ($request->user()?->getAuthIdentifier() ?? $request->ip());

            return Limit::perMinute(10)->by($key);
        });
    }
}
