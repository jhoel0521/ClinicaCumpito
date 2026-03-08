<?php

namespace App\Policies;

use App\Models\LaboratoryTemplate;
use App\Models\User;

class LaboratoryTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->doctor_id !== null;
    }

    public function update(User $user, LaboratoryTemplate $template): bool
    {
        return $user->doctor_id !== null
            && $user->doctor_id === $template->doctor_id;
    }

    public function delete(User $user, LaboratoryTemplate $template): bool
    {
        return $user->doctor_id !== null
            && $user->doctor_id === $template->doctor_id;
    }
}
