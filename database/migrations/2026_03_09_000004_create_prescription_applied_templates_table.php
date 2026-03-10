<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_applied_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('prescription_id');
            $table->uuid('template_id')->nullable();
            $table->string('template_name');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->foreign('prescription_id')
                ->references('id')
                ->on('prescriptions')
                ->onDelete('cascade');

            $table->foreign('template_id')
                ->references('id')
                ->on('prescription_templates')
                ->onDelete('set null');
        });

        // Remove the single source_template_id FK (replaced by the pivot table)
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['source_template_id']);
            $table->dropColumn('source_template_id');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->uuid('source_template_id')->nullable();
            $table->foreign('source_template_id')
                ->references('id')
                ->on('prescription_templates')
                ->onDelete('set null');
        });

        Schema::dropIfExists('prescription_applied_templates');
    }
};
