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
        Schema::create('oms_catalogo_graficas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->text('descripcion')->nullable();
            $table->string('tipo_grafica'); // peso_talla|talla_edad|peso_edad|perimetro_cefalico|imc
            $table->string('rango_edad');   // texto libre, ej. "0-24 meses"
            $table->string('sexo');         // M|F
            $table->integer('minimo_z_score')->default(-3);
            $table->integer('maximo_z_score')->default(3);
            $table->integer('minimo_percentil')->default(3);
            $table->integer('maximo_percentil')->default(97);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oms_catalogo_graficas');
    }
};
