<?php

use App\DTOs\DoctorDTO;
use App\Http\Requests\UpdateDoctorRequest;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $full_name = '';
    public string $specialty = '';
    public string $license_number = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        if ($doctor) {
            $this->full_name = $doctor->full_name;
            $this->specialty = $doctor->specialty ?? '';
            $this->license_number = $doctor->license_number->value();
        } else {
            // Si el usuario es doctor pero no tiene registro en la tabla doctors (raro), lo inicializamos con su nombre
            $this->full_name = $user->name;
        }
    }

    /**
     * Update the professional doctor information.
     */
    public function updateDoctorInformation(DoctorService $service): void
    {
        // Validamos usando las reglas del Form Request
        $request = new UpdateDoctorRequest();
        $validated = $this->validate($request->rules(), $request->messages());

        $user = Auth::user();
        $doctor = $user->doctor;

        $dto = DoctorDTO::fromArray($validated);

        if ($doctor) {
            $service->update($doctor->id, $dto);
        } else {
            // Si no existe, lo creamos vinculado al usuario actual
            $data = array_merge($validated, ['user_id' => $user->id]);
            $service->create(DoctorDTO::fromArray($data));
        }

        $this->dispatch('doctor-profile-updated');
    }
}; ?>

<section class="w-full">
    <flux:heading class="sr-only">{{ __('Doctor Professional Settings') }}</flux:heading>

    <x-pages::settings.layout
        :heading="__('Información Profesional')"
        :subheading="__('Actualiza tu especialidad y número de matrícula médica')"
    >
        <form wire:submit="updateDoctorInformation" class="my-6 w-full space-y-6">
            <flux:input
                wire:model="full_name"
                :label="__('Nombre Completo')"
                type="text"
                required
                autocomplete="name"
            />

            <flux:input
                wire:model="specialty"
                :label="__('Especialidad')"
                type="text"
                placeholder="Ej. Pediatría, Cardiología..."
            />

            <flux:input
                wire:model="license_number"
                :label="__('Número de Matrícula / Licencia')"
                type="text"
                required
                placeholder="Matrícula profesional"
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Guardar Cambios') }}
                </flux:button>

                <x-action-message class="me-3" on="doctor-profile-updated">
                    {{ __('Perfil actualizado.') }}
                </x-action-message>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
