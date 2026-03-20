<?php

use App\Contracts\ScannedConsultationServiceContract;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $step = 'patient';

    public string $fullName = '';

    public ?Patient $patient = null;

    public string $scanDate = '';

    /** @var mixed */
    public $scanFile = null;

    /** @var array<int, array{name: string, date: string}> */
    public array $uploadedScans = [];

    public function createPatient(): void
    {
        $this->validate(['fullName' => 'required|string|max:255']);

        $patient = Patient::create(['full_name' => $this->fullName]);

        $this->patient = $patient;
        $this->step = 'scans';
    }

    public function uploadScan(): void
    {
        if (! $this->patient) {
            $this->addError('patient', 'Primero crea el paciente antes de subir archivos.');

            return;
        }

        $this->validate([
            'scanFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'scanDate' => 'required|date|before_or_equal:today',
        ]);

        $patient = $this->patient;

        /** @var \App\Models\User $uploader */
        $uploader = auth()->user();

        $consultation = app(ScannedConsultationServiceContract::class)->createFromScan(
            $patient,
            $this->scanFile,
            $this->scanDate,
            $uploader,
        );

        $this->uploadedScans[] = [
            'name' => $consultation->scanned_file_name ?? basename((string) $consultation->scanned_file_path),
            'date' => $this->scanDate,
        ];

        $this->reset(['scanFile', 'scanDate']);
    }
}; ?>

<div>
    {{-- PASO 1: Crear paciente con solo el nombre --}}
    @if ($step === 'patient')
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-teal-700 dark:text-teal-400">Cargar Historia Clínica Antigua</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Ingresa el nombre del paciente para empezar a cargar sus consultas escaneadas. Los datos clínicos
                    (fecha de nacimiento, sexo) se pueden completar después.
                </p>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Nombre completo del paciente
                </label>
                <input
                    type="text"
                    wire:model="fullName"
                    wire:keydown.enter="createPatient"
                    placeholder="Ej. María González Ríos"
                    autofocus
                    dusk="input-full-name-old"
                    class="w-full px-4 py-2.5 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-teal-500 focus:border-teal-500"
                />
                @error('fullName')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button
                    type="button"
                    wire:click="createPatient"
                    wire:loading.attr="disabled"
                    dusk="btn-continuar-old"
                    class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 disabled:opacity-60 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition"
                >
                    <span wire:loading.remove wire:target="createPatient">Continuar →</span>
                    <span wire:loading wire:target="createPatient">Creando...</span>
                </button>
            </div>
        </div>

        {{-- PASO 2: Subir scans uno por uno --}}
    @else
        <div class="space-y-6">
            {{-- Header con nombre del paciente --}}
            <div class="bg-teal-600 dark:bg-teal-700 rounded-xl p-5 text-white">
                <p class="text-xs text-teal-200 uppercase tracking-wide font-medium mb-1">Paciente creado</p>
                <h2 class="text-xl font-bold">{{ $fullName }}</h2>
                <p class="text-xs text-teal-100 mt-1">
                    Ahora carga las consultas escaneadas una por una. Puedes completar sus datos clínicos desde el
                    perfil del paciente.
                </p>
            </div>

            {{-- Lista de scans ya subidos --}}
            @if (count($uploadedScans) > 0)
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">
                        Consultas cargadas ({{ count($uploadedScans) }})
                    </h3>
                    <ul class="space-y-2">
                        @foreach ($uploadedScans as $scan)
                            <li class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                                <span class="text-teal-500">✓</span>
                                <span class="font-medium">{{ $scan['name'] }}</span>
                                <span class="text-zinc-400 text-xs">·</span>
                                <span class="text-zinc-500 text-xs">
                                    {{ \Carbon\Carbon::parse($scan['date'])->format('d/m/Y') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulario para el próximo scan --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5 space-y-4">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 border-b dark:border-zinc-700 pb-2">
                    Agregar consulta escaneada
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Fecha de la consulta original
                        </label>
                        <input
                            type="date"
                            wire:model="scanDate"
                            max="{{ date('Y-m-d') }}"
                            dusk="input-scan-date-old"
                            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-teal-500 focus:border-teal-500"
                        />
                        @error('scanDate')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Archivo (PDF, JPG, PNG — máx. 20 MB)
                        </label>
                        <input
                            type="file"
                            wire:model="scanFile"
                            accept=".pdf,.jpg,.jpeg,.png"
                            dusk="input-scan-file-old"
                            class="w-full text-sm text-zinc-700 dark:text-zinc-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 dark:file:bg-teal-900/40 dark:file:text-teal-300 hover:file:bg-teal-100"
                        />
                        <div wire:loading wire:target="scanFile" class="mt-1 text-xs text-teal-600">
                            Subiendo archivo...
                        </div>
                        @error('scanFile')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        wire:click="uploadScan"
                        wire:loading.attr="disabled"
                        dusk="btn-subir-scan-old"
                        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition"
                    >
                        <span wire:loading.remove wire:target="uploadScan">↑ Subir este scan</span>
                        <span wire:loading wire:target="uploadScan">Subiendo...</span>
                    </button>
                </div>
            </div>

            {{-- Botón finalizar --}}
            <div class="flex justify-between items-center pt-2">
                <p class="text-xs text-zinc-400 dark:text-zinc-500">
                    @if (count($uploadedScans) === 0)
                        Sube al menos una consulta antes de finalizar.
                    @else
                        {{ count($uploadedScans) }} consulta(s) pendiente(s) de digitalizar.
                    @endif
                </p>
                <a
                    href="{{ $patient ? route('pacientes.show', $patient) : '#' }}"
                    dusk="btn-finalizar-old"
                    @class([
                        'inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded-lg text-sm transition',
                        'bg-teal-600 hover:bg-teal-700 text-white' => count($uploadedScans) > 0,
                        'bg-zinc-200 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed pointer-events-none' => count($uploadedScans) === 0,
                    ])
                >
                    → Finalizar — ver paciente
                </a>
            </div>
        </div>
    @endif
</div>
