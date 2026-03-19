<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_applied_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laboratory_request_id');
            $table->uuid('template_id')->nullable();
            $table->string('template_name');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->foreign('laboratory_request_id')
                ->references('id')
                ->on('laboratory_requests')
                ->onDelete('cascade');

            $table->foreign('template_id')
                ->references('id')
                ->on('laboratory_templates')
                ->onDelete('set null');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_applied_templates');
    }
};
