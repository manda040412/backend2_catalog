<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crosses', function (Blueprint $table) {
            $table->id('id_cross');
            $table->string('product_id', 15);
            $table->string('cross_brand', 255)->nullable();
            $table->string('cross_item_code', 255)->nullable();
            $table->string('cross_nama_produk', 255)->nullable();
            $table->string('oem_number', 255);
            $table->timestamps();

            $table->foreign('product_id')->references('id_produk')->on('products')->cascadeOnDelete();
            $table->index('oem_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crosses');
    }
};
