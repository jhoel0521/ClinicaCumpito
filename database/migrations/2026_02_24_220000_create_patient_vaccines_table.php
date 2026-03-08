<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_vaccines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->uuid('consultation_id')->nullable();
            $table->uuid('vaccine_id');
            $table->uuid('applied_by_doctor_id')->nullable();
            $table->string('application_site')->nullable();
            $table->timestamp('applied_at');
            $table->unsignedTinyInteger('dose_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');

            $table->foreign('consultation_id')
                ->references('id')
                ->on('consultations')
                ->onDelete('set null');

            $table->foreign('vaccine_id')
                ->references('id')
                ->on('vaccines')
                ->onDelete('restrict');

            $table->foreign('applied_by_doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');

            $table->index(['consultation_id', 'applied_at']);
            $table->index(['patient_id', 'applied_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_vaccines');
    }
};
