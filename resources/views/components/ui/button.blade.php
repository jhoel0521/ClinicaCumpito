@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];

    $variants = [
        'primary' => 'bg-teal-600 text-white hover:bg-teal-700 focus:ring-teal-500 dark:bg-teal-700 dark:hover:bg-teal-600',
        'secondary' => 'bg-zinc-200 text-zinc-700 hover:bg-zinc-300 focus:ring-zinc-400 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600',
        'ghost' => 'bg-transparent text-zinc-700 hover:bg-zinc-100 focus:ring-zinc-400 dark:text-zinc-300 dark:hover:bg-zinc-800',
    ];

    $classes = trim($base . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'data-ui' => 'button']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'data-ui' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
