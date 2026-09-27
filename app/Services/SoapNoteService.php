<?php

namespace App\Services;

use App\Contracts\SoapNoteServiceContract;
use App\DTOs\SoapNoteDTO;
use App\Models\Consultation;
use App\Models\SoapNote;
use App\ValueObjects\ConsultationStatus;

class SoapNoteService implements SoapNoteServiceContract
{
    public function upsert(string $consultationId, SoapNoteDTO $dto): SoapNote
    {
        $this->ensureNotFinalized($consultationId);

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
        $this->ensureNotFinalized($consultationId);

        $soapNote = SoapNote::where('consultation_id', $consultationId)->first();

        if (! $soapNote) {
            return false;
        }

        return (bool) $soapNote->delete();
    }

    /**
     * Inmutabilidad clínica: se valida contra la BD, no contra el estado del
     * componente (una pestaña vieja o un guardado que llega después de
     * "Finalizar" no debe modificar una consulta finalizada).
     */
    private function ensureNotFinalized(string $consultationId): void
    {
        $consultation = Consultation::findOrFail($consultationId);

        if ($consultation->status instanceof ConsultationStatus && $consultation->status->isFinalized()) {
            throw new \DomainException('La consulta está finalizada: la nota SOAP ya no se puede modificar.');
        }
    }
}
