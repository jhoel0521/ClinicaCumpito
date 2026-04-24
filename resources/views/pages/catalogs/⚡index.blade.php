<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="p-6">
    <div class="mb-8">
        <flux:heading size="xl" level="1">{{ __('Catálogos Clínicos') }}</flux:heading>
        <flux:subheading size="lg">
            {{ __('Gestiona los datos maestros para laboratorios, medicamentos, vacunas y condiciones médicas.') }}
        </flux:subheading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <flux:card class="flex flex-col gap-4 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3">
                <flux:icon name="beaker" class="w-8 h-8 text-blue-500" />
                <flux:heading size="lg">{{ __('Laboratorios') }}</flux:heading>
            </div>
            <flux:text>{{ __('Gestiona categorías de laboratorio y exámenes específicos.') }}</flux:text>
            <flux:button variant="primary" :href="route('settings.catalogs.laboratories')" wire:navigate>
                {{ __('Administrar') }}
            </flux:button>
        </flux:card>

        <flux:card class="flex flex-col gap-4 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3">
                <flux:icon name="beaker" class="w-8 h-8 text-purple-500" />
                <flux:heading size="lg">{{ __('Vacunas') }}</flux:heading>
            </div>
            <flux:text>{{ __('Esquema de vacunación (PAI Bolivia) y otros.') }}</flux:text>
            <flux:button variant="primary" :href="route('settings.catalogs.vaccines')" wire:navigate>
                {{ __('Administrar') }}
            </flux:button>
        </flux:card>

        <flux:card class="flex flex-col gap-4 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3">
                <flux:icon name="heart" class="w-8 h-8 text-red-500" />
                <flux:heading size="lg">{{ __('Condiciones Médicas') }}</flux:heading>
            </div>
            <flux:text>{{ __('Condiciones médicas disponibles para el historial de pacientes.') }}</flux:text>
            <flux:button variant="primary" :href="route('settings.catalogs.medical-conditions')" wire:navigate>
                {{ __('Administrar') }}
            </flux:button>
        </flux:card>

        <flux:card class="flex flex-col gap-4 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3">
                <flux:icon name="chart-bar" class="w-8 h-8 text-green-500" />
                <flux:heading size="lg">{{ __('Gráficas OMS') }}</flux:heading>
            </div>
            <flux:text>{{ __('Catálogo de boletas OMS para seguimiento del crecimiento infantil.') }}</flux:text>
            <flux:button variant="primary" :href="route('settings.catalogs.oms-graficas')" wire:navigate>
                {{ __('Administrar') }}
            </flux:button>
        </flux:card>
    </div>
</section>
