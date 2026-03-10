<?php

namespace App\Models;

use Database\Factories\LaboratoryTemplateItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryTemplateItem extends Model
{
    /** @use HasFactory<LaboratoryTemplateItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'template_id',
        'laboratory_exam_id',
        'custom_exam_name',
        'indications',
    ];

    public function getExamNameAttribute(): string
    {
        if ($this->custom_exam_name !== null) {
            return $this->custom_exam_name;
        }

        return $this->exam !== null ? $this->exam->name : '';
    }

    /**
     * @return BelongsTo<LaboratoryTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTemplate::class, 'template_id');
    }

    /**
     * @return BelongsTo<LaboratoryExam, $this>
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(LaboratoryExam::class, 'laboratory_exam_id');
    }
}
