<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_request_items', function (Blueprint $table) {
            $table->string('result_value')->nullable()->after('indications');
            $table->boolean('is_abnormal')->default(false)->after('result_value');
            $table->text('result_notes')->nullable()->after('is_abnormal');
            $table->timestamp('result_received_at')->nullable()->after('result_notes');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_request_items', function (Blueprint $table) {
            $table->dropColumn(['result_value', 'is_abnormal', 'result_notes', 'result_received_at']);
        });
    }
};
