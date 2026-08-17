<?php

namespace App\Contracts;

use App\Models\LaboratoryAttachment;
use Illuminate\Http\UploadedFile;

interface LaboratoryAttachmentServiceContract
{
    public function replaceForRequest(string $laboratoryRequestId, UploadedFile $file): LaboratoryAttachment;

    public function deleteForRequest(string $laboratoryRequestId, string $attachmentId): bool;

    public function deleteAllForRequest(string $laboratoryRequestId): void;
}
