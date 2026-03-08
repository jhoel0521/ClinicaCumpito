@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        {{
            $attributes->merge([
                'class' => 'w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm transition focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100',
                'data-ui' => 'input',
            ])
        }}
    />

    @error($name)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
