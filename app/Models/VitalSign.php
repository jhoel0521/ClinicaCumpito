<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vital_signs';

    protected $casts = [
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'head_circumference' => 'decimal:2',
        'temperature' => 'decimal:2',
    ];

    protected $fillable = [
        'consultation_id',
        'weight',
        'height',
        'head_circumference',
        'temperature',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
