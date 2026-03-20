<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryAttachment extends Model
{
    use HasUuids;

    protected $table = 'laboratory_attachments';

    protected $fillable = [
        'laboratory_request_id',
        'laboratory_request_item_id',
        'file_path',
        'original_name',
        'mime_type',
        'sort_order',
    ];

    /** @return BelongsTo<LaboratoryRequest, $this> */
    public function laboratoryRequest(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class);
    }

    /** @return BelongsTo<LaboratoryRequestItem, $this> */
    public function laboratoryRequestItem(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequestItem::class);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function url(): string
    {
        return asset('storage/'.$this->file_path);
    }
}
