<?php

namespace App\Models;

use App\ValueObjects\LicenseNumber;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'doctors';

    protected $casts = [
        'active' => 'boolean',
        'license_number' => LicenseNumber::class,
    ];

    protected $fillable = [
        'user_id',
        'full_name',
        'specialty',
        'license_number',
        'active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }
}
