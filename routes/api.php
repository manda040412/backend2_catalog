<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\TrashController;
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

        Route::middleware('approved')->group(function () {
            // Search dropdowns — hanya user yang sudah approved
            Route::get('/search/dropdown/brands',  [SearchController::class, 'dropdownBrands']);
            Route::get('/search/dropdown/types',   [SearchController::class, 'dropdownTypes']);
            Route::get('/search/dropdown/years',   [SearchController::class, 'dropdownYears']);
            Route::get('/search/dropdown/bodies',  [SearchController::class, 'dropdownCarBodies']);
            Route::get('/search/dropdown/engines', [SearchController::class, 'dropdownEngines']);

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

            // Products CRUD (tanpa delete — delete khusus SADM)
            Route::post('/products',      [ProductController::class, 'store']);
            Route::put('/products/{id}',  [ProductController::class, 'update']);

            // Categories CRUD (tanpa delete)
            Route::post('/categories',     [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);

            // Crosses CRUD (tanpa delete)
            Route::get('/crosses',         [CrossController::class, 'index']);
            Route::post('/crosses',        [CrossController::class, 'store']);
            Route::put('/crosses/{id}',    [CrossController::class, 'update']);

            // Match Cars CRUD (tanpa delete)
            Route::get('/match-cars',       [MatchCarController::class, 'index']);
            Route::post('/match-cars',      [MatchCarController::class, 'store']);
            Route::put('/match-cars/{id}',  [MatchCarController::class, 'update']);

            // User management (ADM: tidak bisa delete)
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
            // Hanya SADM yang bisa menghapus data (soft delete)
            Route::delete('/products/{id}',       [ProductController::class, 'destroy']);
            Route::delete('/categories/{id}',     [CategoryController::class, 'destroy']);
            Route::delete('/crosses/{id}',        [CrossController::class, 'destroy']);
            Route::delete('/match-cars/{id}',     [MatchCarController::class, 'destroy']);
            Route::delete('/admin/users/{id}',    [UserManagementController::class, 'destroy']);

            // Trash (Recycle Bin) — list / restore / force delete
            Route::get('/trash/{type}',            [TrashController::class, 'index']);
            Route::patch('/trash/{type}/{id}',     [TrashController::class, 'restore']);
            Route::delete('/trash/{type}/{id}',    [TrashController::class, 'forceDelete']);

            // Export CSV
            Route::get('/export/{type}',           [ExportController::class, 'export']);

            // Import CSV
            Route::post('/import/{type}',          [ImportController::class, 'import']);
        });
    });
});

// ─────────────────────────────────────────────────────────────────
// DEBUG ROUTE — Hapus setelah masalah trash teridentifikasi
// GET /api/debug-trash → test apakah SoftDeletes berfungsi
// ─────────────────────────────────────────────────────────────────
Route::get('/debug-trash', function () {
    try {
        $tests = [];

        // Test 1: apakah kolom deleted_at ada?
        $tests['products_deleted_at_exists'] = \Illuminate\Support\Facades\Schema::hasColumn('products', 'deleted_at');
        $tests['users_deleted_at_exists']    = \Illuminate\Support\Facades\Schema::hasColumn('users', 'deleted_at');

        // Test 2: apakah SoftDeletes bisa dipanggil?
        $tests['products_onlyTrashed_count'] = \App\Models\Product::onlyTrashed()->count();
        $tests['categories_onlyTrashed_count'] = \App\Models\Category::onlyTrashed()->count();
        $tests['crosses_onlyTrashed_count']  = \App\Models\Cross::onlyTrashed()->count();
        $tests['match_cars_onlyTrashed_count'] = \App\Models\MatchCar::onlyTrashed()->count();
        $tests['users_onlyTrashed_count']    = \App\Models\User::onlyTrashed()->count();

        return response()->json(['status' => 'OK', 'tests' => $tests]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'message' => $e->getMessage(),
            'file'   => $e->getFile(),
            'line'   => $e->getLine(),
        ], 500);
    }
});