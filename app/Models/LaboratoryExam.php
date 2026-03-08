<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryExam extends Model
{
    /** @use HasFactory<\Database\Factories\LaboratoryExamFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'unit',
        'reference_range',
    ];

    /**
     * Get the category that owns the exam.
     *
     * @return BelongsTo<LaboratoryCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(LaboratoryCategory::class, 'category_id');
    }
}
