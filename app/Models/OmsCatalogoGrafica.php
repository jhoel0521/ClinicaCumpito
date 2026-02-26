<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OmsCatalogoGrafica extends Model
{
    /** @use HasFactory<\Database\Factories\OmsCatalogoGraficaFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'tipo_grafica',
        'rango_edad',
        'sexo',
        'minimo_z_score',
        'maximo_z_score',
        'minimo_percentil',
        'maximo_percentil',
    ];

    /** @return HasMany<OmsDatoGrafica, $this> */
    public function datos(): HasMany
    {
        return $this->hasMany(OmsDatoGrafica::class, 'oms_catalogo_grafica_id')
            ->orderBy('x_value');
    }
}
