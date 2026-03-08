<?php

namespace App\Models;

use App\ValueObjects\LicenseNumber;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'doctors';

    protected $casts = [
        'active' => 'boolean',
        'license_number' => LicenseNumber::class,
    ];

    protected $fillable = [
        'full_name',
        'specialty',
        'license_number',
        'active',
    ];

    /** @return HasOne<User, $this> */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'doctor_id');
    }

    /** @return HasMany<Consultation, $this> */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    /** @return HasMany<PrescriptionTemplate, $this> */
    public function prescriptionTemplates(): HasMany
    {
        return $this->hasMany(PrescriptionTemplate::class, 'doctor_id');
    }

    /** @return HasMany<LaboratoryTemplate, $this> */
    public function laboratoryTemplates(): HasMany
    {
        return $this->hasMany(LaboratoryTemplate::class, 'doctor_id');
    }
}
