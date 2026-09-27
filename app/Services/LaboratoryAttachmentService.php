<?php

namespace App\Services;

use App\Contracts\LaboratoryAttachmentServiceContract;
use App\Models\LaboratoryAttachment;
use App\Models\LaboratoryRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LaboratoryAttachmentService implements LaboratoryAttachmentServiceContract
{
    public function replaceForRequest(string $laboratoryRequestId, UploadedFile $file): LaboratoryAttachment
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = Str::uuid().'.'.$extension;
        $directory = 'lab-attachments/'.$laboratoryRequestId;
        $path = $directory.'/'.$fileName;

        $stored = $file->storeAs($directory, $fileName, LaboratoryAttachment::DISK);

        if ($stored === false) {
            throw new \RuntimeException('No se pudo guardar el archivo del estudio.');
        }

        $previousPaths = collect();

        try {
            $attachment = DB::transaction(function () use ($laboratoryRequestId, $file, $path, &$previousPaths) {
                $request = LaboratoryRequest::query()
                    ->whereKey($laboratoryRequestId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $request->isPending()) {
                    throw new \DomainException('Solo se puede reemplazar el archivo de una solicitud pendiente.');
                }

                $previous = $this->queryForRequest($laboratoryRequestId)->get();
                $previousPaths = $previous->pluck('file_path');
                $previous->each->delete();

                return LaboratoryAttachment::create([
                    'laboratory_request_id' => $laboratoryRequestId,
                    'laboratory_request_item_id' => null,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'sort_order' => 0,
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk(LaboratoryAttachment::DISK)->delete($path);

            throw $exception;
        }

        $previousPaths
            ->reject(fn (string $previousPath): bool => $previousPath === $path)
            ->each(fn (string $previousPath) => $this->deleteFile($previousPath));

        return $attachment;
    }

    public function deleteForRequest(string $laboratoryRequestId, string $attachmentId): bool
    {
        $request = LaboratoryRequest::findOrFail($laboratoryRequestId);

        if (! $request->isPending()) {
            throw new \DomainException('Solo se puede eliminar el archivo de una solicitud pendiente.');
        }

        $attachment = $this->queryForRequest($laboratoryRequestId)->findOrFail($attachmentId);
        $this->deleteFile($attachment->file_path);

        return (bool) $attachment->delete();
    }

    public function deleteAllForRequest(string $laboratoryRequestId): void
    {
        $attachments = $this->queryForRequest($laboratoryRequestId)->get();

        $attachments->each(function (LaboratoryAttachment $attachment): void {
            $this->deleteFile($attachment->file_path);
            $attachment->delete();
        });
    }

    /**
     * Borra el archivo del disco privado y, si es un adjunto antiguo, también
     * del disco público donde se guardaban antes.
     */
    private function deleteFile(string $path): void
    {
        Storage::disk(LaboratoryAttachment::DISK)->delete($path);
        Storage::disk(LaboratoryAttachment::LEGACY_DISK)->delete($path);
    }

    /** @return Builder<LaboratoryAttachment> */
    private function queryForRequest(string $laboratoryRequestId): Builder
    {
        return LaboratoryAttachment::query()
            ->where(function (Builder $query) use ($laboratoryRequestId): void {
                $query
                    ->where('laboratory_request_id', $laboratoryRequestId)
                    ->orWhereHas(
                        'laboratoryRequestItem',
                        fn (Builder $itemQuery) => $itemQuery->where('laboratory_request_id', $laboratoryRequestId),
                    );
            });
    }
}
