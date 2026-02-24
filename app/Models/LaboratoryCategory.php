<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryCategory extends Model
{
    /** @use HasFactory<\Database\Factories\LaboratoryCategoryFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the exams for this category.
     *
     * @return HasMany<LaboratoryExam, $this>
     */
    public function exams(): HasMany
    {
        return $this->hasMany(LaboratoryExam::class, 'category_id');
    }
}
