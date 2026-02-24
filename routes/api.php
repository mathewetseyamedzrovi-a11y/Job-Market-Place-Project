<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    // Job routes - now protected by auth
    Route::apiResource('jobs', JobController::class);
});

Route::get('/test', function() {
    return response()->json(['message' => 'API test working!']);
});

// Categories route (public)
Route::get('/categories', [JobController::class, 'categories']);