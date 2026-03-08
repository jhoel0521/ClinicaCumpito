@props([
    'id',
    'title' => 'Detalle',
    'triggerText' => null,
])

<div {{ $attributes->merge(['class' => 'inline-block']) }}>
    @if ($triggerText)
        <x-ui.button type="button" variant="secondary" onclick="document.getElementById('{{ $id }}').showModal()">
            {{ $triggerText }}
        </x-ui.button>
    @endif

    <dialog
        id="{{ $id }}"
        class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white p-0 backdrop:bg-black/40 dark:border-zinc-700 dark:bg-zinc-900"
        data-ui="modal"
    >
        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
        </div>

        <div class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
            {{ $slot }}
        </div>

        <div class="flex justify-end border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <x-ui.button type="button" variant="secondary" onclick="document.getElementById('{{ $id }}').close()">
                Cerrar
            </x-ui.button>
        </div>
    </dialog>
</div>
