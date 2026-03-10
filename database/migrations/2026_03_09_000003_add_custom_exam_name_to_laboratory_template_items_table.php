<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_template_items', function (Blueprint $table) {
            // Make FK nullable to allow free-text exams
            $table->dropForeign(['laboratory_exam_id']);
            $table->uuid('laboratory_exam_id')->nullable()->change();
            $table->foreign('laboratory_exam_id')
                ->references('id')
                ->on('laboratory_exams')
                ->onDelete('set null');

            $table->string('custom_exam_name')->nullable()->after('laboratory_exam_id');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_template_items', function (Blueprint $table) {
            $table->dropColumn('custom_exam_name');

            $table->dropForeign(['laboratory_exam_id']);
            $table->uuid('laboratory_exam_id')->nullable(false)->change();
            $table->foreign('laboratory_exam_id')
                ->references('id')
                ->on('laboratory_exams')
                ->onDelete('cascade');
        });
    }
};
