<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('id_produk', 15)->primary();
            $table->string('category_id', 10)->nullable();
            $table->string('item_code', 255);
            $table->string('brand_produk', 255);
            $table->string('nama_produk', 255);
            $table->string('print_description', 255)->nullable();
            $table->tinyInteger('is_internal_only')->default(0);
            $table->timestamps();

            $table->foreign('category_id')->references('id_category')->on('categories')->nullOnDelete();
            $table->index('item_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
