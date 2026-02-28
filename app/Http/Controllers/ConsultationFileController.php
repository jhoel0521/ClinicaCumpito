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

        return Storage::disk('local')->response($path, $name);
    }
}
