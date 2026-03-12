<?php

use App\Http\Controllers\AuthController;
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
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Future versioned routes:
    // GET/POST /api/v1/boards
    // GET/PUT/DELETE /api/v1/boards/{board}
    // GET/POST /api/v1/boards/{board}/columns
    // ...
});
