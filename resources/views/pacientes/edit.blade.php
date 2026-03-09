<x-layouts::app :title="__('Editar Paciente')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-teal-700 dark:text-teal-400">Editar Paciente</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">{{ $patient->full_name }}</p>
            </div>
            <a
                href="{{ route('pacientes.show', $patient->id) }}"
                class="bg-gray-200 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-700 transition flex items-center gap-2"
            >
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>

        @if (request('require_complete') || session('require_complete'))
            <div
                class="mb-6 rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-4 text-sm text-amber-800 dark:text-amber-300"
            >
                <strong>Datos incompletos.</strong>
                Completa la fecha de nacimiento y el sexo del paciente para poder crear consultas digitales y usar las
                gráficas OMS.
            </div>
        @endif

        <form action="{{ route('pacientes.update', $patient->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('pacientes._form', ['patient' => $patient])

            <div class="flex justify-end space-x-4 pt-4">
                <a
                    href="{{ route('pacientes.show', $patient->id) }}"
                    class="px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-teal-600 dark:bg-teal-700 text-white rounded-lg hover:bg-teal-700 dark:hover:bg-teal-600 transition"
                >
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Paciente
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
