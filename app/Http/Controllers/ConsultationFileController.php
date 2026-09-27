<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsultationFileController extends Controller
{
    public function serve(Consultation $consulta): StreamedResponse|Response
    {
        $this->authorize('view', $consulta);

        if (! $consulta->hasScannedFile()) {
            abort(404);
        }

        /** @var string $path */
        $path = $consulta->scanned_file_path;

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        /** @var string $name */
        $name = $consulta->scanned_file_name ?? basename($path);

        $response = Storage::disk('local')->response($path, $name);

        // El escaneo de una consulta no cambia: con internet lento conviene
        // que el navegador lo reutilice en vez de descargarlo cada vez.
        $response->headers->set('Cache-Control', 'private, max-age=86400');

        return $response;
    }
}
