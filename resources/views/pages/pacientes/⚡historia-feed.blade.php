<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Patient;
use App\Models\Consultation;

new class extends Component {
    use WithPagination;

    public Patient $patient;
    public int $perPage = 5;

    public function mount(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function loadMore()
    {
        $this->perPage += 5;
    }

    public function with(): array
    {
        return [
            'consultations' => Consultation::with([
                'doctor',
                'soapNote',
                'vitalSigns',
                'prescriptions',
                'laboratoryRequests',
            ])
                ->where('patient_id', $this->patient->id)
                ->whereIn('status', ['saved', 'finalized'])
                ->orderByDesc('consultation_date')
                ->paginate($this->perPage),
        ];
    }
};
?>

<div>
    <div
        class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
        x-data="{
        observe() {
            let observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        @this.call('loadMore')
                    }
                })
            }, { rootMargin: '200px' })
            observer.observe(this.$refs.loadMoreTrigger)
        }
    }"
        x-init="observe"
    >
        {{-- Header del Feed --}}
        <div
            class="flex items-center justify-between bg-white/60 dark:bg-zinc-900/60 backdrop-blur-md rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-sm sticky top-4 z-20"
        >
            <div class="flex items-center gap-4">
                <flux:avatar size="xl" :name="$patient->full_name" />
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $patient->full_name }}</h1>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm">
                        {{ $patient->age()?->forDisplayFull() ?? 'Edad desconocida' }} ·
                        {{ $patient->gender ? ($patient->gender->value() === 'M' ? 'Masculino' : 'Femenino') : '—' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <flux:button
                    href="{{ route('pacientes.show', $patient) }}"
                    variant="outline"
                    size="sm"
                    icon="arrow-left"
                >
                    Volver al perfil
                </flux:button>
            </div>
        </div>

        {{-- El Feed --}}
        <div
            class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-zinc-300 dark:before:via-zinc-700 before:to-transparent"
        >
            @forelse ($consultations as $consultation)
                <div
                    class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active"
                >
                    {{-- Icono central --}}
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-zinc-900 bg-teal-500 dark:bg-teal-600 text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10"
                    >
                        <flux:icon.clipboard-document-check class="size-4" />
                    </div>

                    {{-- Tarjeta de consulta --}}
                    <div
                        class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white dark:bg-zinc-900 p-5 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 transition-all hover:shadow-md"
                    >
                        {{-- Cabecera Tarjeta --}}
                        <div
                            class="flex items-start justify-between mb-3 border-b border-zinc-100 dark:border-zinc-800 pb-3"
                        >
                            <div>
                                <div class="text-sm text-teal-600 dark:text-teal-400 font-bold mb-1">
                                    {{ $consultation->consultation_date->isoFormat('D [de] MMMM YYYY') }}
                                </div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white leading-tight">
                                    {{ $consultation->soapNote->subjective ?? 'Consulta Médica' }}
                                </h3>
                                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-1">
                                    <flux:icon.user class="size-3" />
                                    Dr. {{ $consultation->doctor?->full_name ?? 'Cumpito' }}
                                </div>
                            </div>
                            <flux:button
                                href="{{ route('consultas.show', $consultation) }}"
                                size="xs"
                                variant="subtle"
                                icon="arrow-top-right-on-square"
                            >
                                Abrir
                            </flux:button>
                        </div>

                        @php
                            $hasPrescription = $consultation->prescriptions->isNotEmpty();
                            $hasLaboratoryRequest = $consultation->laboratoryRequests->isNotEmpty();
                            $hasPendingLaboratories = $consultation->laboratoryRequests->contains(
                                fn ($request) => $request->isPending(),
                            );
                        @endphp

                        {{-- Resumen clínico de la consulta --}}
                        <div class="mb-4 flex flex-wrap gap-2" dusk="consultation-summary-{{ $consultation->id }}">
                            @if ($consultation->soapNote)
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-700 ring-1 ring-inset ring-teal-200 dark:bg-teal-900/30 dark:text-teal-300 dark:ring-teal-800"
                                >
                                    <flux:icon.check-circle class="size-3.5" />
                                    SOAP registrado
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700"
                                >
                                    SOAP pendiente
                                </span>
                            @endif

                            @if ($hasPrescription)
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800"
                                >
                                    <flux:icon.document-text class="size-3.5" />
                                    Receta emitida
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700"
                                >
                                    Sin receta
                                </span>
                            @endif

                            @if ($hasLaboratoryRequest)
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 ring-1 ring-inset ring-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:ring-violet-800"
                                >
                                    <flux:icon.beaker class="size-3.5" />
                                    Laboratorio solicitado
                                </span>

                                @if ($hasPendingLaboratories)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800"
                                    >
                                        Laboratorios pendientes
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800"
                                    >
                                        Laboratorios recibidos
                                    </span>
                                @endif
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700"
                                >
                                    Sin laboratorio
                                </span>
                            @endif
                        </div>

                        {{-- Contenido SOAP --}}
                        @if ($consultation->soapNote)
                            <div class="space-y-3 mb-4 text-sm text-zinc-700 dark:text-zinc-300">
                                @if ($consultation->soapNote->objective)
                                    <div>
                                        <span class="font-semibold text-zinc-900 dark:text-white">Físico:</span>
                                        {{ Str::limit($consultation->soapNote->objective, 150) }}
                                    </div>
                                @endif

                                @if ($consultation->soapNote->assessment)
                                    <div
                                        class="bg-teal-50 dark:bg-teal-900/20 p-2 rounded-lg border border-teal-100 dark:border-teal-800/50"
                                    >
                                        <span class="font-semibold text-teal-800 dark:text-teal-300">Dx:</span>
                                        {{ $consultation->soapNote->assessment }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-zinc-500">
                    <flux:icon.inbox class="size-12 mx-auto mb-3 opacity-20" />
                    <p>No hay historial clínico registrado.</p>
                </div>
            @endforelse

            {{-- Trigger para Infinite Scroll --}}
            @if ($consultations->hasMorePages())
                <div x-ref="loadMoreTrigger" class="flex justify-center py-6">
                    <flux:icon.arrow-path class="size-6 animate-spin text-zinc-400" />
                </div>
            @endif
        </div>

        {{-- Botón Flotante Volver Arriba --}}
        <button
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-8 right-8 p-3 rounded-full bg-teal-600 text-white shadow-lg shadow-teal-600/30 hover:bg-teal-700 transition hover:-translate-y-1 z-50"
            title="Volver arriba"
        >
            <flux:icon.chevron-up class="size-5" />
        </button>
    </div>
</div>
