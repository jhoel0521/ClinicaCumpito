<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaboratoryAttachmentController extends Controller
{
    /**
     * Sirve un adjunto de laboratorio solo a usuarios autorizados a ver la
     * consulta. Los resultados clínicos ya no son públicos en /storage.
     */
    public function show(LaboratoryAttachment $attachment): StreamedResponse
    {
        $consultation = $attachment->owningConsultation();

        if ($consultation === null) {
            abort(404);
        }

        $this->authorize('view', $consultation);

        $disk = $attachment->storageDisk();

        if ($disk === null) {
            abort(404);
        }

        $response = Storage::disk($disk)->response(
            $attachment->file_path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type],
        );

        // El archivo nunca cambia (reemplazar crea otro con nuevo UUID):
        // con internet lento conviene que el navegador lo reutilice.
        $response->headers->set('Cache-Control', 'private, max-age=604800, immutable');

        return $response;
    }
}
