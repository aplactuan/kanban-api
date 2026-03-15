<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Board\DestroyBoardController;
use App\Http\Controllers\Board\IndexBoardController;
use App\Http\Controllers\Board\ShowBoardController;
use App\Http\Controllers\Board\StoreBoardController;
use App\Http\Controllers\Board\UpdateBoardController;
use App\Http\Controllers\Column\DestroyBoardColumnController;
use App\Http\Controllers\Column\IndexBoardColumnController;
use App\Http\Controllers\Column\StoreBoardColumnController;
use App\Http\Controllers\Column\UpdateBoardColumnController;
use App\Http\Controllers\Task\DestroyColumnTaskController;
use App\Http\Controllers\Task\IndexColumnTaskController;
use App\Http\Controllers\Task\MoveTaskController;
use App\Http\Controllers\Task\StoreColumnTaskController;
use App\Http\Controllers\Task\UpdateColumnTaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Unversioned: auth only
// POST /api/register
// POST /api/login
// POST /api/logout
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Versioned API (v1)
Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::get('/boards', IndexBoardController::class);
        Route::post('/boards', StoreBoardController::class);
        Route::get('/boards/{board}', ShowBoardController::class);
        Route::put('/boards/{board}', UpdateBoardController::class);
        Route::delete('/boards/{board}', DestroyBoardController::class);

        Route::get('/boards/{board}/columns', IndexBoardColumnController::class);
        Route::post('/boards/{board}/columns', StoreBoardColumnController::class);
        Route::put('/boards/{board}/columns/{column}', UpdateBoardColumnController::class);
        Route::delete('/boards/{board}/columns/{column}', DestroyBoardColumnController::class);

        Route::get('/boards/{board}/columns/{column}/tasks', IndexColumnTaskController::class);
        Route::post('/boards/{board}/columns/{column}/tasks', StoreColumnTaskController::class);
        Route::put('/boards/{board}/columns/{column}/tasks/{task}', UpdateColumnTaskController::class);
        Route::delete('/boards/{board}/columns/{column}/tasks/{task}', DestroyColumnTaskController::class);
        Route::patch('/tasks/{task}/move', MoveTaskController::class);
    });
});
