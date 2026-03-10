<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryAppliedTemplate extends Model
{
    use HasUuids;

    protected $table = 'laboratory_applied_templates';

    protected $fillable = [
        'laboratory_request_id',
        'template_id',
        'template_name',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /** @return BelongsTo<LaboratoryRequest, $this> */
    public function laboratoryRequest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class);
    }

    /** @return BelongsTo<LaboratoryTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTemplate::class, 'template_id');
    }
}
