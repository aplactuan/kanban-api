<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Board\DestroyBoardController;
use App\Http\Controllers\Board\IndexBoardController;
use App\Http\Controllers\Board\ShowBoardController;
use App\Http\Controllers\Board\StoreBoardController;
use App\Http\Controllers\Board\UpdateBoardController;
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
    });
});
