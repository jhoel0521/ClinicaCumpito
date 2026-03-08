<x-layouts::app :title="__('Pacientes')">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pacientes</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Gestión de pacientes del sistema</p>
            </div>
            <div class="flex gap-2">
                <a
                    href="{{ route('pacientes.create-old') }}"
                    class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-4 py-2 rounded-lg text-sm transition"
                >
                    <i class="fas fa-file-upload"></i>
                    Cargar Historia Antigua
                </a>
                <x-ui.button :href="route('pacientes.create')" variant="primary">
                    <i class="fas fa-plus"></i>
                    Nuevo Paciente
                </x-ui.button>
            </div>
        </div>

        <!-- Mensajes Flash -->
        @if (session('success'))
            <x-ui.alert type="success" class="mb-4">
                <p class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </p>
            </x-ui.alert>
        @endif

        <!-- Componente de Búsqueda y Lista (Livewire) -->
        <livewire:patient-list />
    </div>
</x-layouts::app>
