<?php

namespace App\Models;

use Database\Factories\PrescriptionTemplateItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionTemplateItem extends Model
{
    /** @use HasFactory<PrescriptionTemplateItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'template_id',
        'medication_id',
        'custom_medication_name',
        'dose',
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
     * @return BelongsTo<Medication, $this>
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
