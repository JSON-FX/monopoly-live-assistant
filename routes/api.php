<?php

use App\Http\Controllers\SessionController;
use App\Http\Controllers\SpinController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sessions', [SessionController::class, 'store']);
    Route::get('/sessions/{id}', [SessionController::class, 'show']);
    Route::put('/sessions/{id}/close', [SessionController::class, 'close']);
    Route::post('/sessions/{sessionId}/spins', [SpinController::class, 'store']);
}); 