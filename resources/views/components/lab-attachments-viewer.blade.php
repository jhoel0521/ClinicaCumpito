@props(['items' => [], 'canDelete' => false, 'title' => '', 'requestId' => null])

@php
    $items = collect($items)
        ->map(
            fn ($item) => [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? ($item['original_name'] ?? 'Archivo'),
                'url' => $item['url'],
                'type' => $item['type'] ?? ($item['is_pdf'] ?? false ? 'pdf' : 'image'),
            ],
        )
        ->values()
        ->all();
@endphp

<div
    x-data="{
        viewerOpen: false,
        viewerIndex: 0,
        items: @js($items),
        openViewer(index) {
            this.viewerIndex = index
            this.viewerOpen = true
        },
        next() {
            this.viewerIndex = (this.viewerIndex + 1) % this.items.length
        },
        prev() {
            this.viewerIndex =
                (this.viewerIndex - 1 + this.items.length) % this.items.length
        },
    }"
>
    {{-- Thumbnails / tarjetas --}}
    <div class="flex flex-wrap gap-3">
        @foreach ($items as $index => $item)
            <div class="group relative">
                @if ($item['type'] === 'pdf')
                    <button
                        type="button"
                        @click="openViewer({{ $index }})"
                        class="flex items-center gap-2 px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition text-left max-w-52"
                        title="Ver {{ $item['name'] }}"
                    >
                        <flux:icon.document-text class="size-5 text-red-500 shrink-0" />
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate">
                            {{ $item['name'] }}
                        </span>
                    </button>
                @else
                    <div class="flex flex-col items-center gap-1">
                        <button
                            type="button"
                            @click="openViewer({{ $index }})"
                            class="block rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:border-sky-400 dark:hover:border-sky-600 hover:shadow-md transition"
                            title="Ver {{ $item['name'] }}"
                        >
                            <img
                                src="{{ $item['url'] }}"
                                alt="{{ $item['name'] }}"
                                class="h-20 w-20 object-cover"
                                loading="lazy"
                            />
                        </button>
                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400 max-w-20 truncate leading-tight">
                            {{ $item['name'] }}
                        </span>
                    </div>
                @endif

                @if ($canDelete && $requestId && $item['id'])
                    <button
                        wire:click="deleteAttachment('{{ $requestId }}', '{{ $item['id'] }}')"
                        data-swal-confirm="¿Eliminar este archivo adjunto?"
                        class="absolute -top-1.5 -right-1.5 p-1 rounded-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-400 hover:text-red-500 transition shadow-sm"
                        title="Eliminar adjunto"
                    >
                        <flux:icon.x-mark class="size-3" />
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Visor (modal) --}}
    <div
        x-show="viewerOpen"
        x-cloak
        @keydown.escape.window="viewerOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="viewerOpen = false"></div>

        <div class="relative z-10 w-full max-w-5xl">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-white/90 truncate mr-4" x-text="items[viewerIndex]?.name ?? ''"></p>
                <button
                    @click="viewerOpen = false"
                    class="p-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition"
                    title="Cerrar (Esc)"
                >
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-xl overflow-hidden shadow-2xl">
                <template x-if="items[viewerIndex]?.type === 'pdf'">
                    <iframe
                        :src="items[viewerIndex].url"
                        class="w-full h-[78vh] border-0"
                        title="Vista previa del documento"
                    ></iframe>
                </template>
                <template x-if="items[viewerIndex]?.type === 'image'">
                    <div class="flex items-center justify-center h-[78vh] bg-zinc-950">
                        <img
                            :src="items[viewerIndex].url"
                            :alt="items[viewerIndex].name ?? ''"
                            class="max-h-full max-w-full object-contain"
                        />
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-center gap-4 mt-3">
                <button
                    @click="prev()"
                    class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-sm transition"
                >
                    ‹ Anterior
                </button>
                <span class="text-white/80 text-sm" x-text="viewerIndex + 1 + ' / ' + items.length"></span>
                <button
                    @click="next()"
                    class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-sm transition"
                >
                    Siguiente ›
                </button>
            </div>
        </div>
    </div>
</div>
