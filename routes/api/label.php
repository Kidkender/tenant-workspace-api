<?php

use App\Modules\Labels\Http\Controllers\LabelController;
use Illuminate\Support\Facades\Route;

Route::prefix('labels')->group(function () {
    Route::get('/', [LabelController::class, 'index']);
    Route::post('/', [LabelController::class, 'store']);
    Route::put('/{id}', [LabelController::class, 'update']);
    Route::delete('/{id}', [LabelController::class, 'destroy']);
});
