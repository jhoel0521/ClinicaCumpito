<?php

namespace App\Models;

use Database\Factories\PrescriptionTemplateFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionTemplate extends Model
{
    /** @use HasFactory<PrescriptionTemplateFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * @return HasMany<PrescriptionTemplateItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionTemplateItem::class, 'template_id');
    }
}
