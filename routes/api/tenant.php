<?php

use App\Http\Middleware\ResolveTenant;
use App\Modules\Access\RoleController;
use App\Modules\Tenant\TenantController;
use Illuminate\Support\Facades\Route;

Route::post('/tenants', [TenantController::class, 'store']);
Route::post('/tenants/accept/{token}', [TenantController::class, 'accept']);

Route::middleware(ResolveTenant::class)->scopeBindings()->group(function () {
    Route::put('/tenants/settings', [TenantController::class, 'update']);
    Route::post('/tenants/invite', [TenantController::class, 'invite']);

    Route::prefix('tenant/members')->group(function () {
        Route::get('/', [TenantController::class, 'members']);
        Route::delete('/{userId}', [TenantController::class, 'removeMember']);
        Route::put('/{userId}/role', [TenantController::class, 'updateMemberRole']);
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/custom', [RoleController::class, 'store']);
        Route::put('/custom/{roleId}', [RoleController::class, 'update']);
        Route::delete('/custom/{roleId}', [RoleController::class, 'destroy']);
    });

    Route::get('/permissions', [RoleController::class, 'permissions']);
});
