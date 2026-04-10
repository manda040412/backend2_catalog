<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ApprovalController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Catalog\ProductController;
use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────
// PUBLIC — tidak perlu token sama sekali
// ─────────────────────────────────────────────────────────────────
Route::get('/roles', [RoleController::class, 'index']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ─────────────────────────────────────────────────────────────────
// SEMI-AUTH — perlu token (dapat saat register/login), tapi belum
// perlu 2FA verified. Dipakai untuk kirim/verifikasi kode 2FA.
// ─────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout',              [AuthController::class, 'logout']);
    Route::get('/me',                   [AuthController::class, 'me']);
    Route::post('/two-factor/send',     [TwoFactorController::class, 'send']);
    Route::post('/two-factor/verify',   [TwoFactorController::class, 'verify']);
});

// ─────────────────────────────────────────────────────────────────
// AUTHENTICATED + 2FA VERIFIED
// ─────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', '2fa.verified'])->group(function () {

    // Dropdown untuk form search (bisa diakses meski belum approved)
    Route::get('/search/dropdown/brands', [SearchController::class, 'dropdownBrands']);
    Route::get('/search/dropdown/types',  [SearchController::class, 'dropdownTypes']);
    Route::get('/categories',             [CategoryController::class, 'index']);
    Route::get('/categories/{id}',        [CategoryController::class, 'show']);

    // ─── PERLU APPROVED ──────────────────────────────────────────
    Route::middleware('approved')->group(function () {

        // Search & Catalog
        Route::get('/search/product',     [SearchController::class, 'searchProduct']);
        Route::get('/search/application', [SearchController::class, 'searchApplication']);
        Route::get('/products',           [ProductController::class, 'index']);
        Route::get('/products/{id}',      [ProductController::class, 'show']);

        // ─── ADMIN (ADM atau SADM) ───────────────────────────────
        Route::middleware('role:ADM,SADM')->group(function () {
            Route::get('/admin/approvals',        [ApprovalController::class, 'index']);
            Route::patch('/admin/approvals/{id}', [ApprovalController::class, 'process']);

            Route::post('/products',              [ProductController::class, 'store']);
            Route::put('/products/{id}',          [ProductController::class, 'update']);
            Route::delete('/products/{id}',       [ProductController::class, 'destroy']);

            Route::post('/categories',            [CategoryController::class, 'store']);
            Route::put('/categories/{id}',        [CategoryController::class, 'update']);
            Route::delete('/categories/{id}',     [CategoryController::class, 'destroy']);
        });

        // ─── SUPER ADMIN (SADM only) ─────────────────────────────
        Route::middleware('role:SADM')->group(function () {
            Route::get('/admin/users',          [UserManagementController::class, 'index']);
            Route::post('/admin/users',         [UserManagementController::class, 'store']);
            Route::put('/admin/users/{id}',     [UserManagementController::class, 'update']);
            Route::delete('/admin/users/{id}',  [UserManagementController::class, 'destroy']);
            Route::get('/admin/activity-logs',  [ActivityLogController::class, 'index']);
        });
    });
});
