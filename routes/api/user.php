<?php

use App\Modules\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/me', [UserController::class, 'getMe']);
Route::put('/me', [UserController::class, 'updateMe']);
