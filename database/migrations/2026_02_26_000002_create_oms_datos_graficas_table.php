<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oms_datos_graficas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('oms_catalogo_grafica_id')
                ->constrained('oms_catalogo_graficas')
                ->cascadeOnDelete();
            $table->decimal('x_value', 8, 4);          // eje X: edad meses o longitud cm
            $table->decimal('l_value', 10, 6);          // LMS: Box-Cox power
            $table->decimal('m_value', 10, 6);          // LMS: mediana
            $table->decimal('s_value', 10, 6);          // LMS: coeficiente de variación
            $table->decimal('sd3neg', 8, 4)->nullable(); // -3 SD
            $table->decimal('sd2neg', 8, 4)->nullable(); // -2 SD
            $table->decimal('sd1neg', 8, 4)->nullable(); // -1 SD
            $table->decimal('sd0', 8, 4)->nullable();    // mediana SD
            $table->decimal('sd1', 8, 4)->nullable();    // +1 SD
            $table->decimal('sd2', 8, 4)->nullable();    // +2 SD
            $table->decimal('sd3', 8, 4)->nullable();    // +3 SD
            $table->decimal('p3', 8, 4)->nullable();     // percentil 3
            $table->decimal('p15', 8, 4)->nullable();    // percentil 15
            $table->decimal('p50', 8, 4)->nullable();    // percentil 50
            $table->decimal('p85', 8, 4)->nullable();    // percentil 85
            $table->decimal('p97', 8, 4)->nullable();    // percentil 97
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oms_datos_graficas');
    }
};
