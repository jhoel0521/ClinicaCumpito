<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryExamParameter extends Model
{
    use HasUuids;

    protected $table = 'laboratory_exam_parameters';

    protected $fillable = [
        'exam_id',
        'name',
        'unit',
        'reference_range',
        'sort_order',
    ];

    /** @return BelongsTo<LaboratoryExam, $this> */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(LaboratoryExam::class, 'exam_id');
    }
}
