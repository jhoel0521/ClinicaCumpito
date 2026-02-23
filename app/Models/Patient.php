<?php

namespace App\Models;

use App\ValueObjects\BirthType;
use App\ValueObjects\BloodGroup;
use App\ValueObjects\Gender;
use App\ValueObjects\MedicalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'patients';

    protected $casts = [
        'date_of_birth' => 'date',
        'gender' => Gender::class,
        'blood_group' => BloodGroup::class,
        'birth_type' => BirthType::class,
        'chagas_status' => MedicalStatus::class,
        'syphilis_status' => MedicalStatus::class,
    ];

    protected $fillable = [
        'responsible_doctor_id',
        'user_id',
        'full_name',
        'date_of_birth',
        'gender',
        'birth_weight',
        'birth_height',
        'birth_head_circumference',
        'birth_type',
        'birth_place',
        'blood_group',
        'chagas_status',
        'syphilis_status',
        'allergies',
        'pathologies',
        'surgeries',
    ];

    /** @return BelongsTo<Doctor, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'responsible_doctor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Consultation, $this> */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }
}
