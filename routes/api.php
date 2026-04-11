<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ApprovalController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

Route::get('/roles', [RoleController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-2fa', [AuthController::class, 'verify2FA']);

// ─────────────────────────────────────────────
// PUBLIC ROUTES
// ─────────────────────────────────────────────
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

// ─────────────────────────────────────────────
// AUTHENTICATED ROUTES (Sanctum)
// ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // 2FA — tidak perlu 2FA verified dulu
    Route::post('/auth/two-factor/send',   [TwoFactorController::class, 'send']);
    Route::post('/auth/two-factor/verify', [TwoFactorController::class, 'verify']);

    // ─────────────────────────────────────────
    // ROUTES YANG BUTUH 2FA VERIFIED
    // ─────────────────────────────────────────
    Route::middleware('2fa.verified')->group(function () {

        // Categories (public read)
        Route::get('/categories', [CategoryController::class, 'index']);

        // Dropdown untuk search
        Route::get('/search/dropdown/brands',  [SearchController::class, 'dropdownBrands']);
        Route::get('/search/dropdown/types',   [SearchController::class, 'dropdownTypes']);
        Route::get('/search/dropdown/years',   [SearchController::class, 'dropdownYears']);
        Route::get('/search/dropdown/bodies',  [SearchController::class, 'dropdownCarBodies']);
        Route::get('/search/dropdown/engines', [SearchController::class, 'dropdownEngines']);

        // Search — butuh login + 2FA + approved
        Route::middleware('approved')->group(function () {
            Route::get('/search/product',     [SearchController::class, 'searchProduct']);
            Route::get('/search/application', [SearchController::class, 'searchApplication']);
            Route::get('/products',           [ProductController::class, 'index']);
            Route::get('/products/{id}',      [ProductController::class, 'show']);
        });

        // ─────────────────────────────────────
        // ADMIN ROUTES (ADM atau SADM)
        // ─────────────────────────────────────
        Route::middleware(['approved', 'role:ADM,SADM'])->group(function () {
            Route::get('/admin/approvals',       [ApprovalController::class, 'index']);
            Route::patch('/admin/approvals/{id}',[ApprovalController::class, 'process']);

            Route::post('/products',             [ProductController::class, 'store']);
            Route::put('/products/{id}',         [ProductController::class, 'update']);
            Route::delete('/products/{id}',      [ProductController::class, 'destroy']);

            Route::post('/categories',           [CategoryController::class, 'store']);
            Route::put('/categories/{id}',       [CategoryController::class, 'update']);
            Route::delete('/categories/{id}',    [CategoryController::class, 'destroy']);
        });

        // ─────────────────────────────────────
        // SUPER ADMIN ROUTES (SADM only)
        // ─────────────────────────────────────
        Route::middleware(['approved', 'role:SADM'])->group(function () {
            Route::get('/admin/users',        [UserManagementController::class, 'index']);
            Route::post('/admin/users',       [UserManagementController::class, 'store']);
            Route::put('/admin/users/{id}',   [UserManagementController::class, 'update']);
            Route::delete('/admin/users/{id}',[UserManagementController::class, 'destroy']);
            Route::get('/admin/activity-logs',[ActivityLogController::class, 'index']);
        });
    });
});