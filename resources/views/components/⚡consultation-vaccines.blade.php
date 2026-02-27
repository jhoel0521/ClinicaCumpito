<?php

use App\Contracts\PatientVaccineServiceContract;
use App\DTOs\PatientVaccineDTO;
use App\Models\Consultation;
use App\Models\Vaccine;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;

    /** @var array<int, array{id: string, name: string}> */
    public array $availableVaccines = [];

    public string $doctorId = '';

    /** @var array<int, array{id: string, vaccine_name: string, vaccine_id: string, applied_at: string, dose_number: int|null, notes: string|null}> */
    public array $vaccines = [];

    public string $newVaccineId = '';

    public string $newAppliedAt = '';

    public int $newDoseNumber = 1;

    public string $newNotes = '';

    public bool $finalized = false;

    public string $errorMessage = '';

    public function mount(string $consultationId): void
    {
        $this->consultationId = $consultationId;

        $consultation = Consultation::with('patientVaccines.vaccine')->findOrFail($consultationId);

        $this->availableVaccines = Vaccine::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($v) => ['id' => $v->id, 'name' => $v->name])
            ->all();
        $this->finalized =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->isFinalized()
                : (string) $consultation->status === ConsultationStatus::FINALIZED;

        $this->doctorId = $consultation->doctor_id;
        $this->newAppliedAt = now()->format('Y-m-d\TH:i');

        $this->vaccines = $consultation->patientVaccines
            ->map(
                fn ($pv) => [
                    'id' => $pv->id,
                    'vaccine_id' => $pv->vaccine_id,
                    'vaccine_name' => $pv->vaccine?->name ?? '—',
                    'applied_at' => optional($pv->applied_at)->format('d/m/Y H:i') ?? '—',
                    'dose_number' => $pv->dose_number,
                    'notes' => $pv->notes,
                ],
            )
            ->values()
            ->all();

        if (count($this->availableVaccines) > 0) {
            $this->newVaccineId = $this->availableVaccines[0]['id'];
        }
    }

    public function addVaccine(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        $this->validate(
            [
                'newVaccineId' => ['required', 'string'],
                'newAppliedAt' => ['required', 'string'],
                'newDoseNumber' => ['nullable', 'integer', 'min:1', 'max:20'],
                'newNotes' => ['nullable', 'string', 'max:500'],
            ],
            [
                'newVaccineId.required' => 'Seleccione una vacuna.',
                'newAppliedAt.required' => 'La fecha de aplicación es obligatoria.',
            ],
        );

        try {
            $dto = new PatientVaccineDTO(
                vaccine_id: $this->newVaccineId,
                applied_at: $this->newAppliedAt,
                applied_by_doctor_id: $this->doctorId ?: null,
                application_site: null,
                dose_number: $this->newDoseNumber ?: null,
                notes: $this->newNotes ?: null,
            );

            $pv = app(PatientVaccineServiceContract::class)->create($this->consultationId, $dto);

            $vaccineName = collect($this->availableVaccines)->firstWhere('id', $this->newVaccineId)['name'] ?? '—';

            $this->vaccines[] = [
                'id' => $pv->id,
                'vaccine_id' => $pv->vaccine_id,
                'vaccine_name' => $vaccineName,
                'applied_at' => optional($pv->applied_at)->format('d/m/Y H:i') ?? $this->newAppliedAt,
                'dose_number' => $pv->dose_number,
                'notes' => $pv->notes,
            ];

            $this->newAppliedAt = now()->format('Y-m-d\TH:i');
            $this->newDoseNumber = 1;
            $this->newNotes = '';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al guardar vacuna: ' . $e->getMessage();
        }
    }

    public function removeVaccine(string $vaccineId): void
    {
        if ($this->finalized) {
            return;
        }

        $this->errorMessage = '';

        try {
            app(PatientVaccineServiceContract::class)->delete($vaccineId);
            $this->vaccines = array_values(array_filter($this->vaccines, fn ($v) => $v['id'] !== $vaccineId));
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error al eliminar: ' . $e->getMessage();
        }
    }
}; ?>

<section id="vacunas" dusk="section-vaccines" class="scroll-mt-16">
    <div
        class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center">
                    <svg
                        class="w-4 h-4 text-teal-600 dark:text-teal-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Vacunas Aplicadas</h2>
            </div>
            <div>
                @if ($finalized)
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                    >
                        Finalizada
                    </span>
                @elseif (count($vaccines) > 0)
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300"
                    >
                        {{ count($vaccines) }} {{ count($vaccines) === 1 ? 'vacuna' : 'vacunas' }}
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400"
                    >
                        Sin vacunas
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-5">
            @if ($errorMessage)
                <div
                    class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-300"
                >
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Lista vacunas aplicadas --}}
            @if (count($vaccines) > 0)
                <div class="space-y-2">
                    @foreach ($vaccines as $pv)
                        <div
                            class="flex items-start justify-between bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800/40 rounded-lg px-4 py-3 gap-3"
                            dusk="vaccine-item"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $pv['vaccine_name'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $pv['applied_at'] }}
                                    @if ($pv['dose_number'])
                                        · Dosis #{{ $pv['dose_number'] }}
                                    @endif
                                </p>
                                @if ($pv['notes'])
                                    <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5 italic">
                                        {{ $pv['notes'] }}
                                    </p>
                                @endif
                            </div>
                            @if (! $finalized)
                                <button
                                    wire:click="removeVaccine('{{ $pv['id'] }}')"
                                    wire:loading.attr="disabled"
                                    dusk="vaccine-remove"
                                    class="flex-shrink-0 text-red-400 hover:text-red-600 dark:hover:text-red-300 transition p-1"
                                    title="Eliminar"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-zinc-500">Sin vacunas registradas en esta consulta.</p>
            @endif

            {{-- Formulario agregar vacuna --}}
            @if (! $finalized)
                <div class="bg-gray-50 dark:bg-zinc-800/60 rounded-xl p-4 border border-gray-200 dark:border-zinc-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                        Registrar Vacuna
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                        <div class="lg:col-span-2">
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Vacuna *</label>
                            <select
                                wire:model="newVaccineId"
                                dusk="vaccine-select"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            >
                                @foreach ($availableVaccines as $v)
                                    <option value="{{ $v['id'] }}">{{ $v['name'] }}</option>
                                @endforeach
                            </select>
                            @error('newVaccineId')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                Fecha aplicación *
                            </label>
                            <input
                                wire:model="newAppliedAt"
                                type="datetime-local"
                                dusk="vaccine-applied-at"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            />
                            @error('newAppliedAt')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Dosis #</label>
                            <input
                                wire:model="newDoseNumber"
                                type="number"
                                min="1"
                                max="20"
                                dusk="vaccine-dose-number"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Notas</label>
                        <textarea
                            wire:model="newNotes"
                            rows="2"
                            dusk="vaccine-notes"
                            placeholder="Observaciones sobre la aplicación..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm resize-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        ></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button
                            wire:click="addVaccine"
                            wire:loading.attr="disabled"
                            dusk="vaccine-save-btn"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-zinc-700 border border-teal-300 dark:border-teal-700 text-teal-700 dark:text-teal-300 text-sm font-medium hover:bg-teal-50 dark:hover:bg-teal-900/30 transition disabled:opacity-50"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Guardar Vacuna
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
