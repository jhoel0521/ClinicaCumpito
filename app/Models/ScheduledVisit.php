<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Visita programada por el doctor. Unifica "control extra" y "programar
 * visita": el motivo dice qué es. El cumplimiento se calcula con
 * App\ValueObjects\VisitStatus contra las consultas del paciente.
 */
class ScheduledVisit extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduledVisitFactory> */
    use Auditable, HasFactory, HasUuids;

    protected $table = 'scheduled_visits';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'created_by_user_id',
        'scheduled_for',
        'reason',
    ];

    protected $casts = [
        'scheduled_for' => 'date',
    ];

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return BelongsTo<Doctor, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
