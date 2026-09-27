<?php

namespace App\Livewire\Concerns;

use App\Models\Consultation;

/**
 * Componentes Livewire que editan una consulta.
 *
 * Todo request posterior al montaje (guardar SOAP, agregar receta, finalizar,
 * etc.) exige permiso `update` sobre la consulta, igual que las rutas HTTP.
 * El `consultationId` del componente debe declararse con #[Locked] para que
 * no pueda cambiarse desde el navegador.
 */
trait AuthorizesConsultationEdits
{
    public function hydrateAuthorizesConsultationEdits(): void
    {
        $this->authorize('update', Consultation::findOrFail($this->consultationId));
    }
}
