<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    /** @use HasFactory<\Database\Factories\PrescriptionItemFactory> */
    use HasFactory, HasUuids;

    protected $table = 'prescription_items';

    protected $fillable = [
        'prescription_id',
        'source_template_item_id',
        'medication_name',
        'dose',
        'frequency',
        'duration',
        'instructions',
    ];

    /** @return BelongsTo<Prescription, $this> */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /** @return BelongsTo<PrescriptionTemplateItem, $this> */
    public function sourceTemplateItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionTemplateItem::class, 'source_template_item_id');
    }
}
