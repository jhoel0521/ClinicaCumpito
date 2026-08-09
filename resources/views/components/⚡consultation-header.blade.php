<?php

use App\Contracts\ConsultationServiceContract;
use App\DTOs\ConsultationDTO;
use App\Models\Consultation;
use App\Models\Patient;
use App\ValueObjects\Age;
use App\ValueObjects\ConsultationStatus;
use Livewire\Component;

new class extends Component {
    public string $consultationId;

    public string $consultation_date = '';

    public string $status = '';

    public string $patientName = '';

    public ?Patient $patient = null;

    public string $doctorName = '';

    public string $errorMessage = '';

    public function mount(string $consultationId): void
    {
        $consultation = Consultation::with(['patient', 'doctor'])->findOrFail($consultationId);

        $this->consultationId = $consultationId;
        $this->status =
            $consultation->status instanceof ConsultationStatus
                ? $consultation->status->value()
                : (string) $consultation->status;
        $this->consultation_date = optional($consultation->consultation_date)->format('Y-m-d\TH:i') ?? '';
        $this->patient = $consultation->patient;
        $this->patientName = $consultation->patient->full_name;
        $this->doctorName = $consultation->doctor->full_name;
    }

    /**
     * Edad del paciente al momento de la consulta.
     * En borradores usa la fecha actual (la consulta aún no ocurrió);
     * en guardadas/finalizadas usa la fecha de la consulta.
     */
    public function ageAtConsultation(): ?Age
    {
        if ($this->patient?->date_of_birth === null) {
            return null;
        }

        if ($this->status === ConsultationStatus::FINALIZED || $this->status === ConsultationStatus::SAVED) {
            return $this->patient->ageAt($this->consultation_date ?: null);
        }

        return $this->patient->age();
    }

    public function saveDate(): void
    {
        if ($this->status === ConsultationStatus::FINALIZED) {
            return;
        }

        $this->errorMessage = '';
        $this->validate(
            ['consultation_date' => ['required', 'date']],
            ['consultation_date.required' => 'La fecha es obligatoria.', 'consultation_date.date' => 'Fecha inválida.'],
        );

        try {
            $this->persist($this->status);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    public function finalize(): void
    {
        if ($this->status === ConsultationStatus::FINALIZED) {
            return;
        }

        try {
            $this->persist(ConsultationStatus::FINALIZED);
            $this->redirect(route('consultas.show', $this->consultationId), navigate: true);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);
        }
    }

    private function persist(string $newStatus): void
    {
        $consultation = Consultation::findOrFail($this->consultationId);

        $type =
            $consultation->type instanceof \App\ValueObjects\ConsultationType
                ? $consultation->type->value()
                : (string) $consultation->type;

        $dto = ConsultationDTO::fromArray([
            'patient_id' => $consultation->patient_id,
            'doctor_id' => $consultation->doctor_id,
            'type' => $type,
            'status' => $newStatus,
            'consultation_date' => $this->consultation_date,
            'scanned_file_path' => $consultation->scanned_file_path,
            'pending_transcription' => $consultation->pending_transcription,
        ]);

        app(ConsultationServiceContract::class)->update($this->consultationId, $dto);
    }
}; ?>

@php
    $statusColors = ['draft' => 'bg-gray-400', 'saved' => 'bg-yellow-400', 'finalized' => 'bg-green-400'];
    $statusLabels = ['draft' => 'Borrador', 'saved' => 'Guardada', 'finalized' => 'Finalizada'];
    $isFinalized = $status === 'finalized';
    $isDraft = $status === 'draft';
@endphp

<div
    class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm mb-6 overflow-hidden"
>
    {{-- Banner superior --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-950 px-6 py-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <a
                    href="{{ route('pacientes.show', $patient) }}"
                    class="text-blue-200 hover:text-white text-sm transition mb-1 inline-flex items-center gap-1"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    {{ $patientName }}
                </a>
                <h1 class="text-xl font-bold text-white">Consulta Médica</h1>
                <p class="text-blue-200 text-sm mt-0.5">
                    {{ \Carbon\Carbon::parse($consultation_date)->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Badge de estado actual --}}
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/20 text-white border border-white/30"
                >
                    <span class="w-2 h-2 rounded-full {{ $statusColors[$status] ?? 'bg-gray-400' }}"></span>
                    {{ $statusLabels[$status] ?? $status }}
                </span>

                {{-- Único botón de acción: finalizar (bloquea toda edición) --}}
                @if (! $isFinalized)
                    <button
                        wire:click="finalize"
                        wire:loading.attr="disabled"
                        wire:target="finalize"
                        data-swal-confirm="¿Finalizar la consulta? Esta acción no se puede deshacer."
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-400/90 hover:bg-green-300 text-green-900 transition disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="finalize">Finalizar consulta</span>
                        <span wire:loading wire:target="finalize">Finalizando…</span>
                    </button>
                @endif

                @if ($isDraft)
                    <form
                        action="{{ route('consultas.discard-draft', $consultationId) }}"
                        method="POST"
                        data-swal-form-confirm="¿Descartar este borrador? La consulta no se podrá recuperar."
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-500/90 hover:bg-red-400 text-white transition"
                        >
                            Descartar borrador
                        </button>
                    </form>
                @endif

                <a
                    href="{{ route('consultas.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 hover:bg-white/20 text-blue-200 border border-white/20 transition"
                >
                    Todas las consultas
                </a>
            </div>
        </div>
    </div>

    {{-- Meta info --}}
    <div class="grid grid-cols-1 md:grid-cols-3 divide-x divide-y md:divide-y-0 divide-gray-100 dark:divide-zinc-800">
        <div class="px-5 py-4">
            <p class="text-xs font-medium text-gray-400 dark:text-zinc-500 uppercase tracking-wide mb-1">Doctor</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $doctorName }}</p>
        </div>
        <div class="px-5 py-4">
            <p class="text-xs font-medium text-gray-400 dark:text-zinc-500 uppercase tracking-wide mb-1">Paciente</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $patientName }}</p>
            @if ($this->ageAtConsultation())
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                    @if ($isDraft)
                        Edad actual: {{ $this->ageAtConsultation()->forDisplayPediatric() }}
                    @else
                        Edad en la consulta: {{ $this->ageAtConsultation()->forDisplayPediatric() }}
                    @endif
                </p>
            @endif
        </div>
        <div class="px-5 py-4">
            <p class="text-xs font-medium text-gray-400 dark:text-zinc-500 uppercase tracking-wide mb-1">
                Fecha y hora
                @if (! $isFinalized)
                    <span wire:loading wire:target="saveDate" class="ml-1 text-blue-400 normal-case font-normal">
                        guardando…
                    </span>
                @endif
            </p>
            @if ($isFinalized)
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ \Carbon\Carbon::parse($consultation_date)->format('d/m/Y H:i') }}
                </p>
            @else
                <input
                    wire:model="consultation_date"
                    wire:change="saveDate"
                    type="datetime-local"
                    class="px-2 py-1 text-sm border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                @error('consultation_date')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            @endif
        </div>
    </div>

    @if ($errorMessage)
        <div
            class="px-6 py-3 bg-red-50 dark:bg-red-900/20 border-t border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300"
        >
            {{ $errorMessage }}
        </div>
    @endif
</div>
