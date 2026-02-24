<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientVaccine extends Model
{
    /** @use HasFactory<\Database\Factories\PatientVaccineFactory> */
    use HasFactory, HasUuids;

    protected $table = 'patient_vaccines';

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    protected $fillable = [
        'consultation_id',
        'vaccine_id',
        'applied_at',
        'dose_number',
        'notes',
    ];

    /** @return BelongsTo<Consultation, $this> */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /** @return BelongsTo<Vaccine, $this> */
    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class);
    }
}
