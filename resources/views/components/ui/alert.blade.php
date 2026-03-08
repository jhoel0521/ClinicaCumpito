@props([
    'type' => 'info',
    'title' => null,
])

@php
    $types = [
        'success' => 'border-green-200 bg-green-50 text-green-600 dark:border-green-600 dark:bg-zinc-900 dark:text-green-600',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-600 dark:border-amber-600 dark:bg-zinc-900 dark:text-amber-600',
        'danger' => 'border-red-200 bg-red-50 text-red-600 dark:border-red-600 dark:bg-zinc-900 dark:text-red-600',
        'info' => 'border-sky-200 bg-sky-50 text-sky-600 dark:border-sky-600 dark:bg-zinc-900 dark:text-sky-400',
    ];
@endphp

<div
    {{ $attributes->merge(['class' => 'rounded-lg border p-4 ' . ($types[$type] ?? $types['info']), 'data-ui' => 'alert']) }}
>
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif

    <div class="text-sm">
        {{ $slot }}
    </div>
</div>
