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
        'applied_elsewhere' => 'boolean',
    ];

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'vaccine_id',
        'applied_by_doctor_id',
        'application_site',
        'applied_at',
        'dose_number',
        'notes',
        'applied_elsewhere',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return BelongsTo<Consultation, $this> */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /** @return BelongsTo<Vaccine, $this> */
    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class)->withTrashed();
    }

    /** @return BelongsTo<Doctor, $this> */
    public function appliedByDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'applied_by_doctor_id');
    }
}
