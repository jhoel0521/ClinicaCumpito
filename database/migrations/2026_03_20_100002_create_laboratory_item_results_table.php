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
            $table->string('parameter_name')->nullable(); // "Glóbulos Blancos", null para RX/eco
            $table->string('value')->nullable();           // "6.700", null si solo hay informe
            $table->string('reference_range')->nullable(); // "4.5 - 11.0"
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
