<?php

use App\Http\Middleware\ResolveTenant;
use App\Modules\Billing\PlanController;
use Illuminate\Support\Facades\Route;

Route::get('/plans', [PlanController::class, 'index']);
Route::get('/plans/{id}', [PlanController::class, 'show']);

Route::middleware(['auth:sanctum', ResolveTenant::class])->group(function () {
    Route::get('/subscription', [PlanController::class, 'subscription']);
    Route::post('/subscription', [PlanController::class, 'subscribe']);
});
