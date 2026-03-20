<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryItemResult extends Model
{
    use HasUuids;

    protected $table = 'laboratory_item_results';

    protected $fillable = [
        'laboratory_request_item_id',
        'parameter_name',
        'value',
        'reference_range',
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
}
