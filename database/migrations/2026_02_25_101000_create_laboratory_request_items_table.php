<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laboratory_request_id');
            $table->string('exam_name');
            $table->text('indications')->nullable();
            $table->timestamps();

            $table->foreign('laboratory_request_id')
                ->references('id')
                ->on('laboratory_requests')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_request_items');
    }
};
