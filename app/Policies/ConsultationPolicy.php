<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;

class ConsultationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Consultation $consultation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->doctor_id !== null
            || $user->hasRole('Admin')
            || $user->hasRole('Tecnico');
    }

    public function update(User $user, Consultation $consultation): bool
    {
        // Admin puede editar cualquier consulta
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Técnico pode editar consultas manuales sin doctor asignado
        if ($user->hasRole('Tecnico') && $consultation->doctor_id === null) {
            return true;
        }

        // Doctor solo edita sus propias consultas
        return $user->doctor_id !== null
            && $user->doctor_id === $consultation->doctor_id;
    }

    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->hasRole('Admin');
    }
}
