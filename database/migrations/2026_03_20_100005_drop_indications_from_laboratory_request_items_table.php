<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_request_items', function (Blueprint $table) {
            $table->dropColumn('indications');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_request_items', function (Blueprint $table) {
            $table->text('indications')->nullable()->after('parameter_name');
        });
    }
};
