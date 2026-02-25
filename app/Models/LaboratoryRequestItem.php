<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryRequestItem extends Model
{
    /** @use HasFactory<\Database\Factories\LaboratoryRequestItemFactory> */
    use HasFactory, HasUuids;

    protected $table = 'laboratory_request_items';

    protected $fillable = [
        'laboratory_request_id',
        'exam_name',
        'indications',
    ];

    /** @return BelongsTo<LaboratoryRequest, $this> */
    public function laboratoryRequest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class);
    }
}
