<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_cars', function (Blueprint $table) {
            $table->string('id_match', 20)->primary();
            $table->string('product_id', 20);
            $table->string('item_code')->index();
            $table->string('car_maker')->index();
            $table->string('car_model')->index();
            $table->string('year', 30)->nullable();        // "2008 - 2018" atau "2015 - sekarang"
            $table->string('engine_desc')->nullable();
            $table->string('chassis_code')->nullable();
            $table->string('car_body')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id_produk')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_cars');
    }
};
