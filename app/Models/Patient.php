<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'patients';

    protected $casts = [
        'date_of_birth' => 'date',
        'gender' => 'string',
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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'responsible_doctor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }
}
