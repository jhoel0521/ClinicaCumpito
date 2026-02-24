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
            $table->uuid('consultation_id');
            $table->uuid('vaccine_id');
            $table->timestamp('applied_at');
            $table->unsignedTinyInteger('dose_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('consultation_id')
                ->references('id')
                ->on('consultations')
                ->onDelete('cascade');

            $table->foreign('vaccine_id')
                ->references('id')
                ->on('vaccines')
                ->onDelete('restrict');

            $table->index(['consultation_id', 'applied_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_vaccines');
    }
};
