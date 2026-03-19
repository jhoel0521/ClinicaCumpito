<?php

use App\Models\ClinicSetting;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public string $whatsapp = '';
    public ?string $currentLogoPath = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logo = null;

    public string $successMessage = '';
    public string $errorMessage = '';

    public function mount(): void
    {
        $clinic = ClinicSetting::current();
        $this->name = $clinic->name;
        $this->address = $clinic->address ?? '';
        $this->phone = $clinic->phone ?? '';
        $this->whatsapp = $clinic->whatsapp ?? '';
        $this->currentLogoPath = $clinic->logo_path;
    }

    public function save(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';

        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            $clinic = ClinicSetting::current();
            $data = [
                'name' => $this->name,
                'address' => $this->address ?: null,
                'phone' => $this->phone ?: null,
                'whatsapp' => $this->whatsapp ?: null,
            ];

            if ($this->logo) {
                $path = $this->logo->storeAs('clinic', 'logo.' . $this->logo->getClientOriginalExtension(), 'public');
                $data['logo_path'] = $path;
                $this->currentLogoPath = $path;
            }

            $clinic->update($data);
            $this->logo = null;
            $this->successMessage = 'Configuración guardada correctamente.';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar: ' . $e->getMessage();
        }
    }

    public function removeLogo(): void
    {
        $clinic = ClinicSetting::current();
        if ($clinic->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($clinic->logo_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($clinic->logo_path);
        }
        $clinic->update(['logo_path' => null]);
        $this->currentLogoPath = null;
        $this->successMessage = 'Logo eliminado.';
    }
}; ?>

<section class="w-full">
    <x-pages::settings.layout
        heading="Datos de la Clínica"
        subheading="Esta información aparecerá en el membrete de los PDFs (recetas y laboratorios)."
    >
        @if ($successMessage)
            <div
                class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300"
            >
                {{ $successMessage }}
            </div>
        @endif

        @if ($errorMessage)
            <div
                class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300"
            >
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit="save" class="my-6 w-full space-y-5">
            <flux:input
                wire:model="name"
                label="Nombre de la clínica *"
                type="text"
                required
                placeholder="Ej: Clínica Cumpito"
            />

            <flux:input
                wire:model="address"
                label="Dirección"
                type="text"
                placeholder="Ej: Av. Principal #123, Santa Cruz, Bolivia"
            />

            <flux:input wire:model="phone" label="Teléfono" type="text" placeholder="Ej: +591 3 123-4567" />

            <flux:input wire:model="whatsapp" label="WhatsApp" type="text" placeholder="Ej: +591 70012345" />

            {{-- Logo upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Logo de la clínica
                </label>

                @if ($currentLogoPath && ! $logo)
                    <div class="flex items-center gap-3 mb-2">
                        <img
                            src="{{ asset('storage/' . $currentLogoPath) }}"
                            alt="Logo actual"
                            class="h-14 w-auto rounded border border-gray-200 dark:border-zinc-700 object-contain bg-white p-1"
                        />
                        <button
                            type="button"
                            wire:click="removeLogo"
                            class="text-xs text-red-500 hover:text-red-700 transition"
                        >
                            Eliminar logo
                        </button>
                    </div>
                @endif

                @if ($logo)
                    <div class="mb-2">
                        <img
                            src="{{ $logo->temporaryUrl() }}"
                            alt="Vista previa"
                            class="h-14 w-auto rounded border border-gray-200 object-contain bg-white p-1"
                        />
                        <p class="text-xs text-gray-500 mt-1">Vista previa del nuevo logo</p>
                    </div>
                @endif

                <input
                    type="file"
                    wire:model="logo"
                    accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                />
                <p class="mt-1 text-xs text-gray-400">PNG, JPG o SVG. Máx. 2 MB. Recomendado: fondo transparente.</p>

                @error('logo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4 pt-2">
                <flux:button variant="primary" type="submit" class="w-full">Guardar Cambios</flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
