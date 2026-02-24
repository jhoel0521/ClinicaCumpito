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
        Schema::create('laboratory_exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('laboratory_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_exams');
    }
};
