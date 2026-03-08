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
        Schema::create('laboratory_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->uuid('laboratory_exam_id');
            $table->text('indications')->nullable();
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('laboratory_templates')
                ->onDelete('cascade');

            $table->foreign('laboratory_exam_id')
                ->references('id')
                ->on('laboratory_exams')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_template_items');
    }
};
