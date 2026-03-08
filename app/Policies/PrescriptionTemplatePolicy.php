<?php

namespace App\Policies;

use App\Models\PrescriptionTemplate;
use App\Models\User;

class PrescriptionTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->doctor_id !== null;
    }

    public function update(User $user, PrescriptionTemplate $template): bool
    {
        return $user->doctor_id !== null
            && $user->doctor_id === $template->doctor_id;
    }

    public function delete(User $user, PrescriptionTemplate $template): bool
    {
        return $user->doctor_id !== null
            && $user->doctor_id === $template->doctor_id;
    }
}
