<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unificar "dosis" y "cantidad" en un solo campo de texto libre:
        // la doctora los confundía. Los valores existentes se concatenan
        // para no perder información.
        DB::table('prescription_items')->get()->each(function ($item): void {
            if (! empty($item->quantity)) {
                $newDose = empty($item->dose)
                    ? trim($item->quantity)
                    : trim($item->dose).' · '.trim($item->quantity);

                DB::table('prescription_items')->where('id', $item->id)->update(['dose' => $newDose]);
            }
        });

        Schema::table('prescription_items', function (Blueprint $table): void {
            $table->dropColumn('quantity');
        });

        // Mismo tratamiento en las plantillas de recetas
        DB::table('prescription_template_items')->get()->each(function ($item): void {
            if (! empty($item->quantity)) {
                $newDose = empty($item->dose)
                    ? trim($item->quantity)
                    : trim($item->dose).' · '.trim($item->quantity);

                DB::table('prescription_template_items')->where('id', $item->id)->update(['dose' => $newDose]);
            }
        });

        Schema::table('prescription_template_items', function (Blueprint $table): void {
            $table->dropColumn('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table): void {
            $table->string('quantity')->nullable()->after('dose');
        });

        Schema::table('prescription_template_items', function (Blueprint $table): void {
            $table->string('quantity')->nullable()->after('dose');
        });
    }
};
