<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->uuid('doctor_id');
            $table->enum('type', ['digital', 'manual']);
            $table->enum('status', ['draft', 'saved', 'finalized'])->default('saved');
            $table->dateTime('consultation_date');
            $table->string('scanned_file_path')->nullable();
            $table->boolean('pending_transcription')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');

            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
