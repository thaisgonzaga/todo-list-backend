<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas públicas (sem autenticação)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Rotas protegidas (exigem token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

   // Tasks
    Route::get('/tasks',          [TaskController::class, 'index']);
    Route::post('/tasks',         [TaskController::class, 'store']);
    Route::patch('/tasks/{id}',   [TaskController::class, 'update']);
    Route::delete('/tasks/{id}',  [TaskController::class, 'destroy']);
});
