<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('match_cars', function (Blueprint $table) {
            $table->string('id_match', 20)->primary();
            $table->string('product_id', 15);
            $table->string('car_brand', 255);
            $table->string('car_type', 255);
            $table->string('car_chassis', 255)->nullable();
            $table->string('engine_desc', 255)->nullable();
            $table->string('car_body', 255)->nullable();
            $table->year('year_from')->nullable();
            $table->year('year_to')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id_produk')->on('products')->cascadeOnDelete();
            $table->index(['car_brand', 'car_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_cars');
    }
};
