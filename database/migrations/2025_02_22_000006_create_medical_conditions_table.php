<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique(); // Chagas, Syphilis, HIV, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_medical_conditions', function (Blueprint $table) {
            $table->uuid('patient_id');
            $table->uuid('medical_condition_id');
            $table->enum('status', ['Positive', 'Negative', 'Not tested']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->primary(['patient_id', 'medical_condition_id']);

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');

            $table->foreign('medical_condition_id')
                ->references('id')
                ->on('medical_conditions')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medical_conditions');
        Schema::dropIfExists('medical_conditions');
    }
};
