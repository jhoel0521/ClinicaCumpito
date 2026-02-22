<?php

namespace App\Models;

use App\ValueObjects\ConsultationStatus;
use App\ValueObjects\ConsultationType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'consultations';

    protected $casts = [
        'consultation_date' => 'datetime',
        'pending_transcription' => 'boolean',
        'type' => ConsultationType::class,
        'status' => ConsultationStatus::class,
    ];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'type',
        'status',
        'consultation_date',
        'scanned_file_path',
        'pending_transcription',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function vitalSigns(): HasOne
    {
        return $this->hasOne(VitalSign::class, 'consultation_id');
    }

    public function soapNote(): HasOne
    {
        return $this->hasOne(SoapNote::class, 'consultation_id');
    }
}
