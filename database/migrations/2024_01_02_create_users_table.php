<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('role_id', 10)->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('company', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->tinyInteger('is_approved')->default(0);
            $table->string('two_fa_code', 10)->nullable();
            $table->timestamp('two_fa_expires_at')->nullable();
            $table->timestamp('two_fa_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('role_id')->references('id_role')->on('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
