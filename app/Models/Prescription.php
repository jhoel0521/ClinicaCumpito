<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    /** @use HasFactory<\Database\Factories\PrescriptionFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'prescriptions';

    protected $fillable = [
        'consultation_id',
        'reason',
        'observations',
    ];

    /** @return BelongsTo<Consultation, $this> */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /** @return HasMany<PrescriptionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id');
    }

    /** @return HasMany<PrescriptionAppliedTemplate, $this> */
    public function appliedTemplates(): HasMany
    {
        return $this->hasMany(PrescriptionAppliedTemplate::class, 'prescription_id')->orderBy('applied_at');
    }
}
