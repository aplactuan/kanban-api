<?php

namespace App\Providers;

use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Eloquent\BoardRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BoardRepositoryInterface::class, BoardRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
