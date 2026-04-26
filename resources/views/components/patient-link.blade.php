@props(['patient' => null])

@if ($patient)
    <a
        href="{{ route('pacientes.show', $patient) }}"
        {{ $attributes->class(['font-medium hover:text-teal-600 dark:hover:text-teal-400 transition']) }}
    >
        {{ $patient->full_name }}
    </a>
@else
    <span {{ $attributes }}>—</span>
@endif
