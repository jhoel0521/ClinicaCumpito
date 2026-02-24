<?php

namespace App\Models;

use Database\Factories\LaboratoryTemplateFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryTemplate extends Model
{
    /** @use HasFactory<LaboratoryTemplateFactory> */
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
     * @return HasMany<LaboratoryTemplateItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(LaboratoryTemplateItem::class, 'template_id');
    }
}
