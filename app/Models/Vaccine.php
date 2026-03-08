<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vaccine extends Model
{
    /** @use HasFactory<\Database\Factories\VaccineFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'disease_prevented',
        'recommended_age',
        'dose_sequence',
    ];

    /** @return HasMany<PatientVaccine, $this> */
    public function patientVaccines(): HasMany
    {
        return $this->hasMany(PatientVaccine::class);
    }
}
