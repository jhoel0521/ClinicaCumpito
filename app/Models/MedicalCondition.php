<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MedicalCondition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Obtener los pacientes con esta condición médica
     */
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'patient_medical_conditions')
            ->withPivot(['status', 'notes'])
            ->withTimestamps();
    }
}
