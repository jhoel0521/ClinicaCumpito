<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_requests', function (Blueprint $table) {
            $table->text('presumptive_diagnosis')->nullable()->after('observations');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_requests', function (Blueprint $table) {
            $table->dropColumn('presumptive_diagnosis');
        });
    }
};
