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
        'source_template_id',
        'observations',
    ];

    /** @return BelongsTo<Consultation, $this> */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /** @return BelongsTo<LaboratoryTemplate, $this> */
    public function sourceTemplate(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTemplate::class, 'source_template_id');
    }

    /** @return HasMany<LaboratoryRequestItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(LaboratoryRequestItem::class, 'laboratory_request_id');
    }
}
