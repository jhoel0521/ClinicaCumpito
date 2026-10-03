<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visitas programadas por el doctor ("vuelva en 4 días con el laboratorio",
 * "control de los 15 meses"). El estado (a tiempo / en el mes / no vino) no
 * se guarda: se calcula contra las consultas del paciente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->uuid('doctor_id')->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->date('scheduled_for');
            $table->string('reason', 255);
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');

            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');

            $table->foreign('created_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['patient_id', 'scheduled_for']);
            $table->index(['doctor_id', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_visits');
    }
};
