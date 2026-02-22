<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('responsible_doctor_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['M', 'F']);

            // Birth data
            $table->decimal('birth_weight', 5, 2)->nullable();
            $table->decimal('birth_height', 5, 2)->nullable();
            $table->decimal('birth_head_circumference', 5, 2)->nullable();
            $table->enum('birth_type', ['Normal', 'Cesarean'])->nullable();
            $table->string('birth_place')->nullable();

            // Medical history
            $table->string('blood_group', 5)->nullable();
            $table->enum('chagas_status', ['Positive', 'Negative', 'Not tested'])->nullable();
            $table->enum('syphilis_status', ['Positive', 'Negative', 'Not tested'])->nullable();
            $table->text('allergies')->nullable();
            $table->text('pathologies')->nullable();
            $table->text('surgeries')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('responsible_doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
