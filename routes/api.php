<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\Catalog\CrossController;
use App\Http\Controllers\Catalog\MatchCarController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

// ─────────────────────────────────────────────
// PUBLIC ROUTES
// ─────────────────────────────────────────────
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// ─────────────────────────────────────────────
// AUTHENTICATED ROUTES (Sanctum)
// ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // 2FA
    Route::post('/auth/two-factor/send',   [TwoFactorController::class, 'send']);
    Route::post('/auth/two-factor/verify', [TwoFactorController::class, 'verify']);

    Route::middleware('2fa.verified')->group(function () {

        Route::get('/categories', [CategoryController::class, 'index']);

        // Search dropdowns
        Route::get('/search/dropdown/brands',  [SearchController::class, 'dropdownBrands']);
        Route::get('/search/dropdown/types',   [SearchController::class, 'dropdownTypes']);
        Route::get('/search/dropdown/years',   [SearchController::class, 'dropdownYears']);
        Route::get('/search/dropdown/bodies',  [SearchController::class, 'dropdownCarBodies']);
        Route::get('/search/dropdown/engines', [SearchController::class, 'dropdownEngines']);

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

            // Approvals
            Route::get('/admin/approvals',        [ApprovalController::class, 'index']);
            Route::patch('/admin/approvals/{id}', [ApprovalController::class, 'process']);

            // Products CRUD
            Route::post('/products',      [ProductController::class, 'store']);
            Route::put('/products/{id}',  [ProductController::class, 'update']);

            // Categories CRUD
            Route::post('/categories',          [CategoryController::class, 'store']);
            Route::put('/categories/{id}',      [CategoryController::class, 'update']);
            Route::delete('/categories/{id}',   [CategoryController::class, 'destroy']);

            // Crosses CRUD
            Route::get('/crosses',           [CrossController::class, 'index']);
            Route::post('/crosses',          [CrossController::class, 'store']);
            Route::put('/crosses/{id}',      [CrossController::class, 'update']);
            Route::delete('/crosses/{id}',   [CrossController::class, 'destroy']);

            // Match Cars CRUD
            Route::get('/match-cars',          [MatchCarController::class, 'index']);
            Route::post('/match-cars',         [MatchCarController::class, 'store']);
            Route::put('/match-cars/{id}',     [MatchCarController::class, 'update']);
            Route::delete('/match-cars/{id}',  [MatchCarController::class, 'destroy']);

            // User management (Admin: INT/EXT only; SADM: all roles)
            Route::get('/admin/users',              [UserManagementController::class, 'index']);
            Route::post('/admin/users',             [UserManagementController::class, 'store']);
            Route::put('/admin/users/{id}',         [UserManagementController::class, 'update']);
            Route::patch('/admin/users/{id}/toggle',[UserManagementController::class, 'toggleAccess']);

            // Activity logs
            Route::get('/admin/activity-logs', [ActivityLogController::class, 'index']);
        });

        // ─────────────────────────────────────
        // SUPER ADMIN ROUTES (SADM only)
        // ─────────────────────────────────────
        Route::middleware(['approved', 'role:SADM'])->group(function () {
            Route::delete('/products/{id}',      [ProductController::class, 'destroy']);
            Route::delete('/admin/users/{id}',   [UserManagementController::class, 'destroy']);
        });
    });
});