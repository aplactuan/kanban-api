<?php

namespace App\Providers;

use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\ColumnRepositoryInterface;
use App\Repositories\Eloquent\BoardRepository;
use App\Repositories\Eloquent\ColumnRepository;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
