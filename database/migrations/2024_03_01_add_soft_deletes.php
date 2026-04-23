<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom deleted_at ke semua tabel yang butuh soft delete.
 * Ini AMAN — hanya menambah kolom nullable, tidak mengubah data yang ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tables = ['products', 'categories', 'crosses', 'match_cars', 'users'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes(); // tambah kolom deleted_at TIMESTAMP NULL
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['products', 'categories', 'crosses', 'match_cars', 'users'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};