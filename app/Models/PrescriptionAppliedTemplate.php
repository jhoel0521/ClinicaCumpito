<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionAppliedTemplate extends Model
{
    use HasUuids;

    protected $table = 'prescription_applied_templates';

    protected $fillable = [
        'prescription_id',
        'template_id',
        'template_name',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /** @return BelongsTo<Prescription, $this> */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /** @return BelongsTo<PrescriptionTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(PrescriptionTemplate::class, 'template_id');
    }
}
