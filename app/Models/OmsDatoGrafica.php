<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmsDatoGrafica extends Model
{
    /** @use HasFactory<\Database\Factories\OmsDatoGraficaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'oms_datos_graficas';

    protected $fillable = [
        'oms_catalogo_grafica_id',
        'x_value',
        'l_value',
        'm_value',
        's_value',
        'sd3neg',
        'sd2neg',
        'sd1neg',
        'sd0',
        'sd1',
        'sd2',
        'sd3',
        'p3',
        'p15',
        'p50',
        'p85',
        'p97',
    ];

    /**
     * @return BelongsTo<OmsCatalogoGrafica, $this>
     */
    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(OmsCatalogoGrafica::class, 'oms_catalogo_grafica_id');
    }
}
