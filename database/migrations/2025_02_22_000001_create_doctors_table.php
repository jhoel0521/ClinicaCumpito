<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->unique();
            $table->string('full_name');
            $table->string('specialty')->nullable();
            $table->string('license_number')->unique();
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
