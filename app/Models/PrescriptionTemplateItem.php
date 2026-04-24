<?php

namespace App\Models;

use Database\Factories\PrescriptionTemplateItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionTemplateItem extends Model
{
    /** @use HasFactory<PrescriptionTemplateItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'template_id',
        'custom_medication_name',
        'dose',
        'quantity',
        'frequency',
        'duration',
        'instructions',
    ];

    /**
     * @return BelongsTo<PrescriptionTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(PrescriptionTemplate::class, 'template_id');
    }

    /**
     * @return HasMany<PrescriptionItem, $this>
     */
    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'source_template_item_id');
    }
}
