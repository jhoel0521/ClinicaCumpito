<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LaboratoryRequestFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'laboratory_requests';

    protected $fillable = [
        'consultation_id',
        'observations',
        'status',
    ];

    /** @return BelongsTo<Consultation, $this> */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /** @return HasMany<LaboratoryRequestItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(LaboratoryRequestItem::class, 'laboratory_request_id');
    }

    /** @return HasMany<LaboratoryAppliedTemplate, $this> */
    public function appliedTemplates(): HasMany
    {
        return $this->hasMany(LaboratoryAppliedTemplate::class, 'laboratory_request_id')->orderBy('applied_at');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }
}
