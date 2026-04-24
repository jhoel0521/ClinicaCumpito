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
        Schema::create('prescription_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->string('custom_medication_name')->nullable();
            $table->string('dose')->nullable();
            $table->string('quantity')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('prescription_templates')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_template_items');
    }
};
