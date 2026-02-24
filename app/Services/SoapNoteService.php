<?php

namespace App\Services;

use App\Contracts\SoapNoteServiceContract;
use App\DTOs\SoapNoteDTO;
use App\Models\SoapNote;

class SoapNoteService implements SoapNoteServiceContract
{
    public function upsert(string $consultationId, SoapNoteDTO $dto): SoapNote
    {
        $soapNote = SoapNote::updateOrCreate(
            ['consultation_id' => $consultationId],
            $dto->toArray()
        );

        $freshSoapNote = $soapNote->fresh();
        if (! $freshSoapNote instanceof SoapNote) {
            throw new \RuntimeException('No se pudo refrescar la nota SOAP.');
        }

        return $freshSoapNote;
    }

    public function findByConsultation(string $consultationId): ?SoapNote
    {
        return SoapNote::where('consultation_id', $consultationId)->first();
    }

    public function deleteByConsultation(string $consultationId): bool
    {
        $soapNote = SoapNote::where('consultation_id', $consultationId)->first();

        if (! $soapNote) {
            return false;
        }

        return (bool) $soapNote->delete();
    }
}
