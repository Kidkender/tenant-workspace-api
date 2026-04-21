<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Task\Http\Controllers\TaskController;


Route::post('/tasks', [TaskController::class, 'store']);
