<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_item_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laboratory_request_item_id')
                ->constrained('laboratory_request_items')
                ->cascadeOnDelete();
            $table->foreignUuid('consultation_id')
                ->nullable()
                ->constrained('consultations')
                ->nullOnDelete();
            $table->string('value')->nullable();

            $table->text('report_text')->nullable();       // informe de radiólogo, cultivo, etc.
            $table->boolean('is_abnormal')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_item_results');
    }
};
