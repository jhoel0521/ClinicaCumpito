<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LaboratoryAttachment extends Model
{
    use HasUuids;

    /** Resultados clínicos: disco privado, servido por ruta autenticada. */
    public const DISK = 'local';

    /** Adjuntos subidos antes del cambio vivían en el disco público. */
    public const LEGACY_DISK = 'public';

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
        return route('laboratorios.adjuntos.show', $this);
    }

    /** Disco donde está físicamente el archivo (privado o, si es antiguo, público). */
    public function storageDisk(): ?string
    {
        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            if (Storage::disk($disk)->exists($this->file_path)) {
                return $disk;
            }
        }

        return null;
    }

    /** Consulta dueña del adjunto (vía la solicitud o el ítem de la solicitud). */
    public function owningConsultation(): ?Consultation
    {
        $request = $this->laboratoryRequest ?? $this->laboratoryRequestItem?->laboratoryRequest;

        return $request?->consultation;
    }
}
