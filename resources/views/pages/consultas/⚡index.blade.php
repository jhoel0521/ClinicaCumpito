<?php

use App\Models\Consultation;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';
    public string $status = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public bool $pendingTranscription = false;
    public string $labsFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedPendingTranscription(): void
    {
        $this->resetPage();
    }

    public function updatedLabsFilter(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return [
            'consultations' => Consultation::query()
                ->with(['patient', 'doctor', 'laboratoryRequests'])
                ->when($user->doctor_id, fn ($q) => $q->where('doctor_id', $user->doctor_id))
                ->when(
                    $this->search,
                    fn ($q) => $q->whereHas('patient', fn ($q) => $q->where('full_name', 'like', "%{$this->search}%")),
                )
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('consultation_date', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('consultation_date', '<=', $this->dateTo))
                ->when(
                    $this->pendingTranscription,
                    fn ($q) => $q->where('type', 'manual')->where('pending_transcription', true),
                )
                ->when(
                    $this->labsFilter === 'pending',
                    fn ($q) => $q->whereHas('laboratoryRequests', fn ($q) => $q->where('status', 'pending')),
                )
                ->when(
                    $this->labsFilter === 'received',
                    fn ($q) => $q->whereHas('laboratoryRequests', fn ($q) => $q->where('status', 'received')),
                )
                ->latest('consultation_date')
                ->paginate(15),
        ];
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ __('Consultas') }}</flux:heading>
            <flux:subheading>{{ __('Gestión del flujo clínico de consultas') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('consultas.create')" wire:navigate>
            {{ __('Nueva Consulta') }}
        </flux:button>
    </div>

    {{-- Filtros --}}
    <div class="mb-5 space-y-3">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-56">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Buscar paciente...') }}"
                    icon="magnifying-glass"
                />
            </div>
            <flux:input type="date" wire:model.live="dateFrom" :label="__('Desde')" class="w-40" />
            <flux:input type="date" wire:model.live="dateTo" :label="__('Hasta')" class="w-40" />
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                {{ __('Estado:') }}
            </span>
            @foreach (['' => __('Todos'), 'draft' => __('Borrador'), 'saved' => __('Guardada'), 'finalized' => __('Finalizada')] as $val => $label)
                <flux:button
                    size="sm"
                    :variant="$status === $val ? 'primary' : 'ghost'"
                    wire:click="$set('status', '{{ $val }}')"
                >
                    {{ $label }}
                </flux:button>
            @endforeach

            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide ml-3">
                {{ __('Labs:') }}
            </span>
            @foreach (['' => __('Todos'), 'pending' => __('Pendientes'), 'received' => __('Recibidos')] as $val => $label)
                <flux:button
                    size="sm"
                    :variant="$labsFilter === $val ? 'primary' : 'ghost'"
                    wire:click="$set('labsFilter', '{{ $val }}')"
                >
                    {{ $label }}
                </flux:button>
            @endforeach

            <div class="ml-3">
                <flux:checkbox wire:model.live="pendingTranscription" :label="__('Pendiente transcripción')" />
            </div>
        </div>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800"
    >
        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Paciente') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Doctor') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Tipo') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Estado') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Fecha') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Laboratorios') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                    >
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                @forelse ($consultations as $consultation)
                    @php
                        $rawType = $consultation->getRawOriginal('type');
                        $rawStatus = $consultation->getRawOriginal('status');
                        $statusClasses = match ($rawStatus) {
                            'draft' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'saved' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'finalized' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            default => 'bg-gray-100 text-gray-700',
                        };
                        $statusLabel = match ($rawStatus) {
                            'draft' => __('Borrador'),
                            'saved' => __('Guardada'),
                            'finalized' => __('Finalizada'),
                            default => $rawStatus,
                        };
                    @endphp

                    <tr wire:key="{{ $consultation->id }}">
                        <td class="px-6 py-4 font-medium">
                            <x-patient-link :patient="$consultation->patient" />
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ $consultation->doctor?->full_name ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $rawType === 'digital' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300' }}"
                            >
                                {{ $rawType === 'digital' ? __('Digital') : __('Manual') }}
                                @if ($consultation->pending_transcription)
                                    <span
                                        class="w-1.5 h-1.5 rounded-full bg-orange-400"
                                        title="{{ __('Pendiente transcripción') }}"
                                    ></span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}"
                            >
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ optional($consultation->consultation_date)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            @if ($consultation->laboratoryRequests->isEmpty())
                                <span class="text-gray-400">—</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($consultation->laboratoryRequests as $lab)
                                        @php
                                            $days = (int) $lab->created_at->diffInDays(now());
                                            $isPending = $lab->status === 'pending';
                                            $isUrgent = $isPending && $days >= 3;
                                        @endphp

                                        @if ($isPending)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $isUrgent ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' }}"
                                                title="{{ __('Lab pendiente') }}"
                                            >
                                                {{ $days }}d
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                                                title="{{ __('Resultado recibido') }}"
                                            >
                                                {{ __('ok') }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="eye"
                                :href="route('consultas.show', $consultation->id)"
                                wire:navigate
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                            {{ __('No hay consultas con los filtros seleccionados.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $consultations->links() }}
        </div>
    </div>
</section>
