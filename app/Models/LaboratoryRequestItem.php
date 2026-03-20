<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaboratoryRequestItem extends Model
{
    /** @use HasFactory<\Database\Factories\LaboratoryRequestItemFactory> */
    use HasFactory, HasUuids;

    protected $table = 'laboratory_request_items';

    protected $fillable = [
        'laboratory_request_id',
        'exam_name',
        'parameter_name',
    ];

    /** @return BelongsTo<LaboratoryRequest, $this> */
    public function laboratoryRequest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class);
    }

    /** @return HasMany<LaboratoryItemResult, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(LaboratoryItemResult::class, 'laboratory_request_item_id')
            ->orderBy('sort_order');
    }

    /** @return HasMany<LaboratoryAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(LaboratoryAttachment::class, 'laboratory_request_item_id')
            ->orderBy('sort_order');
    }
}
