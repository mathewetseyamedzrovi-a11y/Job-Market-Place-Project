<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CompletionController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PaymentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Job routes - protected by auth
    Route::apiResource('jobs', JobController::class);
    
    // Application routes
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'apply']);
    Route::get('/jobs/{job}/applications', [ApplicationController::class, 'jobApplications']);
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);
    Route::get('/my-applications', [ApplicationController::class, 'myApplications']);
    
    // Profile routes
    Route::get('/profiles/{userId}', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    
    // Completion & Rating routes
    Route::post('/jobs/{job}/complete', [CompletionController::class, 'complete']);
    Route::post('/jobs/{job}/rate', [CompletionController::class, 'rate']);
    
    // Message routes
    Route::get('/jobs/{job}/messages', [MessageController::class, 'conversation']);
    Route::post('/jobs/{job}/messages', [MessageController::class, 'send']);
    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::patch('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    
    // Payment routes
    Route::post('/jobs/{job}/pay', [PaymentController::class, 'requestPayment']);
    Route::get('/payments/{payment}', [PaymentController::class, 'status']);
    Route::get('/my-payments', [PaymentController::class, 'myPayments']);
    
    // Test route
    Route::post('/test-message/{jobId}', [App\Http\Controllers\Api\TestController::class, 'testMessage']);
});

Route::get('/test', function() {
    return response()->json(['message' => 'API test working!']);
});

// Categories route (public)
Route::get('/categories', [JobController::class, 'categories']);

// Public ratings route
Route::get('/users/{userId}/ratings', [CompletionController::class, 'userRatings']);