<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryItemResult extends Model
{
    /** @use HasFactory<\Database\Factories\LaboratoryItemResultFactory> */
    use HasFactory, HasUuids;

    protected $table = 'laboratory_item_results';

    protected $fillable = [
        'laboratory_request_item_id',
        'consultation_id',
        'value',
        'report_text',
        'is_abnormal',
        'sort_order',
    ];

    protected $casts = [
        'is_abnormal' => 'boolean',
    ];

    /** @return BelongsTo<LaboratoryRequestItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequestItem::class, 'laboratory_request_item_id');
    }

    /** @return BelongsTo<Consultation, $this> */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
