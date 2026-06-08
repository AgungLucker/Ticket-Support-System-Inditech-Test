<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Support\Facades\Route;

// Public: login
Route::post('/login', [AuthController::class, 'login']);

// Protected: (sanctum token required)
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/logout', [AuthController::class, 'logout']);

    // Ticket endpoints
    Route::get('/tickets',                          [TicketApiController::class, 'index']);
    Route::post('/tickets',                         [TicketApiController::class, 'store']);
    Route::get('/tickets/{ticket}',                 [TicketApiController::class, 'show']);
    Route::patch('/tickets/{ticket}/status',        [TicketApiController::class, 'updateStatus']);
    Route::post('/tickets/{ticket}/assign',         [TicketApiController::class, 'assign']);
    Route::post('/tickets/{ticket}/comments',       [TicketApiController::class, 'addComment']);
});
