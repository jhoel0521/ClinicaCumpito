<?php

namespace App\Models;

use App\ValueObjects\Measurements\HeadCircumference;
use App\ValueObjects\Measurements\Height;
use App\ValueObjects\Measurements\Temperature;
use App\ValueObjects\Measurements\Weight;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vital_signs';

    protected $casts = [
        'weight' => Weight::class,
        'height' => Height::class,
        'head_circumference' => HeadCircumference::class,
        'temperature' => Temperature::class,
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
