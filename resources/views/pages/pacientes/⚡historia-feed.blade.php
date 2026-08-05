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
                        class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white dark:bg-zinc-900 p-5 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md"
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
                                    {{ Str::limit($consultation->soapNote?->subjective ?? 'Consulta médica', 72) }}
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
                            $soapCards = [
                                'Subjetivo' => $consultation->soapNote?->subjective,
                                'Objetivo' => $consultation->soapNote?->objective,
                                'Diagnóstico' => $consultation->soapNote?->assessment,
                                'Plan' => $consultation->soapNote?->plan,
                            ];
                            $measurementCards = [
                                'Peso' => $consultation->vitalSigns?->weight,
                                'Talla' => $consultation->vitalSigns?->height,
                                'Temperatura' => $consultation->vitalSigns?->temperature,
                                'P. cefálico' => $consultation->vitalSigns?->head_circumference,
                            ];
                            $measurementBadges = [
                                'Peso' => ['short' => 'P', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                                'Talla' => ['short' => 'T', 'class' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300'],
                                'Temperatura' => ['short' => '°', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
                                'P. cefálico' => ['short' => 'PC', 'class' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300'],
                            ];
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
                                @foreach ($consultation->prescriptions as $prescription)
                                    <a
                                        href="{{ route('consultas.pdf.recetas.single', [$consultation, $prescription]) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200 transition-all duration-200 ease-out hover:-translate-y-px hover:bg-blue-100 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800 dark:hover:bg-blue-900/50 dark:focus:ring-offset-zinc-900"
                                        title="Ver receta"
                                        aria-label="Ver receta"
                                        dusk="view-prescription-{{ $prescription->id }}"
                                    >
                                        <flux:icon.document-text class="size-3.5" />
                                        Receta
                                        <flux:icon.eye class="size-3.5" />
                                    </a>
                                @endforeach
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700"
                                >
                                    Sin receta
                                </span>
                            @endif

                            @if ($hasLaboratoryRequest)
                                @foreach ($consultation->laboratoryRequests as $laboratoryRequest)
                                    <a
                                        href="{{ route('consultas.pdf.laboratorio.single', [$consultation, $laboratoryRequest]) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        @class([
                                            'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset transition-all duration-200 ease-out hover:-translate-y-px hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900',
                                            'bg-amber-50 text-amber-700 ring-amber-200 hover:bg-amber-100 focus:ring-amber-500 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800 dark:hover:bg-amber-900/50' => $laboratoryRequest->isPending(),
                                            'bg-emerald-50 text-emerald-700 ring-emerald-200 hover:bg-emerald-100 focus:ring-emerald-500 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800 dark:hover:bg-emerald-900/50' => $laboratoryRequest->isReceived(),
                                        ])
                                        title="Ver solicitud de laboratorio"
                                        aria-label="Ver solicitud de laboratorio {{ $laboratoryRequest->isPending() ? 'pendiente' : 'recibida' }}"
                                        dusk="view-laboratory-{{ $laboratoryRequest->id }}"
                                    >
                                        <flux:icon.beaker class="size-3.5" />
                                        Laboratorio {{ $laboratoryRequest->isPending() ? 'pendiente' : 'recibido' }}
                                        <flux:icon.eye class="size-3.5" />
                                    </a>
                                @endforeach
                            @else
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700"
                                >
                                    Sin laboratorio
                                </span>
                            @endif
                        </div>

                        {{-- Resumen SOAP --}}
                        @if ($consultation->soapNote)
                            <div class="mb-4 grid gap-2 sm:grid-cols-2" dusk="soap-cards-{{ $consultation->id }}">
                                @foreach ($soapCards as $label => $content)
                                    @if (filled($content))
                                        <article
                                            class="rounded-xl border border-zinc-100 bg-zinc-50/80 p-3 text-sm transition-colors duration-200 ease-out hover:border-teal-200 hover:bg-teal-50/60 dark:border-zinc-800 dark:bg-zinc-950/40 dark:hover:border-teal-900 dark:hover:bg-teal-950/20"
                                        >
                                            <h4
                                                class="mb-1 text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300"
                                            >
                                                {{ $label }}
                                            </h4>
                                            <p class="text-zinc-700 dark:text-zinc-300">
                                                {{ Str::limit($content, 110) }}
                                            </p>
                                        </article>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- Medidas registradas --}}
                        @if ($consultation->vitalSigns)
                            <div
                                class="flex w-fit max-w-full flex-wrap items-center gap-1 rounded-xl border border-zinc-200 bg-zinc-50/80 p-1.5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950/40"
                                dusk="vital-sign-cards-{{ $consultation->id }}"
                            >
                                @foreach ($measurementCards as $label => $value)
                                    @if ($value)
                                        @php($badge = $measurementBadges[$label])
                                        <div class="group/measurement relative">
                                            <div
                                                tabindex="0"
                                                role="note"
                                                aria-label="{{ $label }}: {{ $value }}"
                                                class="flex items-center gap-1.5 rounded-lg border border-transparent bg-white px-1.5 py-1 shadow-sm transition-all duration-200 ease-out hover:-translate-y-px hover:border-zinc-200 hover:shadow dark:bg-zinc-900 dark:hover:border-zinc-700"
                                            >
                                                <span
                                                    @class([
                                                        'flex size-6 shrink-0 items-center justify-center rounded-md text-[10px] font-bold tracking-tight transition-transform duration-200 ease-out group-hover/measurement:scale-105 group-focus-within/measurement:scale-105',
                                                        $badge['class'],
                                                    ])
                                                >
                                                    {{ $badge['short'] }}
                                                </span>
                                                <span
                                                    class="whitespace-nowrap pr-1 text-xs font-semibold text-zinc-700 dark:text-zinc-200"
                                                >
                                                    {{ $value }}
                                                </span>
                                            </div>
                                            <span
                                                role="tooltip"
                                                class="pointer-events-none absolute bottom-[calc(100%+0.45rem)] left-1/2 z-20 -translate-x-1/2 translate-y-1 whitespace-nowrap rounded-md border border-zinc-200 bg-white px-2 py-1 text-[11px] font-medium text-zinc-700 opacity-0 shadow-md transition-all duration-200 ease-out group-hover/measurement:translate-y-0 group-hover/measurement:opacity-100 group-focus-within/measurement:translate-y-0 group-focus-within/measurement:opacity-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                            >
                                                {{ $label }}
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
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
