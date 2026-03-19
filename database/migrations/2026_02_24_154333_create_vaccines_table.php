<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vaccines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('disease_prevented')->nullable();
            $table->string('recommended_age')->nullable();
            $table->integer('dose_sequence')->nullable();
            $table->unsignedSmallInteger('min_age_months')->nullable(); // edad mínima en meses (0 = recién nacido)
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccines');
    }
};
