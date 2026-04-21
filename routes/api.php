<?php

use App\Modules\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Modules\Task\Http\Controllers\TaskController;


Route::post('/tasks', [TaskController::class, 'store']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
