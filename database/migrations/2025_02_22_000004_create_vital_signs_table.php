<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('consultation_id')->unique();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('head_circumference', 5, 2)->nullable();
            $table->decimal('temperature', 4, 2)->nullable();
            $table->timestamps();

            $table->foreign('consultation_id')
                ->references('id')
                ->on('consultations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
