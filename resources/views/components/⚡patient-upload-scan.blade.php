<?php

use App\Contracts\ScannedConsultationServiceContract;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $patientId;

    /** @var mixed */
    public $scanFile = null;

    public string $scanDate = '';

    public function openModal(): void
    {
        $this->dispatch('modal-show', name: 'upload-scan-modal');
    }

    public function upload(): void
    {
        $this->validate([
            'scanFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'scanDate' => 'required|date|before_or_equal:today',
        ]);

        $patient = Patient::findOrFail($this->patientId);

        /** @var \App\Models\User $uploader */
        $uploader = auth()->user();

        app(ScannedConsultationServiceContract::class)->createFromScan(
            $patient,
            $this->scanFile,
            $this->scanDate,
            $uploader,
        );

        $this->reset(['scanFile', 'scanDate']);
        $this->dispatch('modal-close', name: 'upload-scan-modal');
        $this->redirect(route('pacientes.show', $this->patientId));
    }
}; ?>

<div>
    {{-- Botón que abre el modal --}}
    <button
        type="button"
        wire:click="openModal"
        class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-4 py-2 rounded-lg text-sm transition"
        dusk="btn-subir-scan"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
            />
        </svg>
        Subir Consulta Escaneada
    </button>

    {{-- Modal --}}
    <flux:modal name="upload-scan-modal" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Subir Consulta Escaneada</flux:heading>

            <form wire:submit="upload" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Fecha de la consulta original
                    </label>
                    <input
                        type="date"
                        wire:model="scanDate"
                        max="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                    />
                    @error('scanDate')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Archivo (PDF, JPG, PNG — máx. 20 MB)
                    </label>
                    <input
                        type="file"
                        wire:model="scanFile"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 dark:file:bg-teal-900/40 dark:file:text-teal-300 hover:file:bg-teal-100 dark:hover:file:bg-teal-900/60"
                    />
                    <div wire:loading wire:target="scanFile" class="mt-1 text-xs text-teal-600">
                        Subiendo archivo...
                    </div>
                    @error('scanFile')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" type="button">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="upload">
                        <span wire:loading.remove wire:target="upload">Subir y crear consulta</span>
                        <span wire:loading wire:target="upload">Subiendo...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
