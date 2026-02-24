<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<section class="p-6">
    <div class="mb-8">
        <flux:heading size="xl" level="1">{{ __('Plantillas Clínicas') }}</flux:heading>
        <flux:subheading size="lg">
            {{ __('Define esquemas preestablecidos para agilizar tus consultas.') }}
        </flux:subheading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:card class="flex flex-col gap-4 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3">
                <flux:icon name="clipboard-document-list" class="w-8 h-8 text-blue-500" />
                <flux:heading size="lg">{{ __('Plantillas de Receta') }}</flux:heading>
            </div>
            <flux:text>{{ __('Crea grupos de medicamentos frecuentes para diagnósticos comunes.') }}</flux:text>
            <flux:button variant="primary" :href="route('templates.prescriptions')" wire:navigate>
                {{ __('Administrar') }}
            </flux:button>
        </flux:card>

        <flux:card class="flex flex-col gap-4 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-3">
                <flux:icon name="beaker" class="w-8 h-8 text-purple-500" />
                <flux:heading size="lg">{{ __('Plantillas de Laboratorio') }}</flux:heading>
            </div>
            <flux:text>
                {{ __('Configura conjuntos de exámenes y estudios por especialidad o sospecha clínica.') }}
            </flux:text>
            <flux:button variant="primary" :href="route('templates.laboratories')" wire:navigate>
                {{ __('Administrar') }}
            </flux:button>
        </flux:card>
    </div>
</section>
