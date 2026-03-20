<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Exactamente uno de los dos FKs estará seteado
            $table->foreignUuid('laboratory_request_id')
                ->nullable()
                ->constrained('laboratory_requests')
                ->cascadeOnDelete();
            $table->foreignUuid('laboratory_request_item_id')
                ->nullable()
                ->constrained('laboratory_request_items')
                ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable(); // 'image/jpeg', 'application/pdf'
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_attachments');
    }
};
