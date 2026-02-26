<x-layouts::app :title="$patient->full_name">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <!-- Encabezado con datos principales -->
        <div
            class="bg-gradient-to-r from-teal-600 to-teal-700 dark:from-teal-800 dark:to-teal-900 text-white rounded-xl shadow-lg p-6 mb-8"
        >
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-4xl font-bold">{{ $patient->full_name }}</h1>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <p class="text-teal-100 text-sm">Fecha de Nacimiento</p>
                            <p class="text-lg font-semibold">{{ $patient->date_of_birth->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-teal-100 text-sm">Edad</p>
                            <p class="text-lg font-semibold">{{ $patient->age()->forDisplay() }}</p>
                        </div>
                        <div>
                            <p class="text-teal-100 text-sm">Género</p>
                            <p class="text-lg font-semibold">
                                @if ($patient->gender->value() === 'M')
                                    <span
                                        class="inline-block bg-blue-200 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm"
                                    >
                                        Masculino
                                    </span>
                                @else
                                    <span
                                        class="inline-block bg-pink-200 dark:bg-pink-900 text-pink-800 dark:text-pink-200 px-3 py-1 rounded-full text-sm"
                                    >
                                        Femenino
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-teal-100 text-sm">Grupo Sanguíneo</p>
                            <p class="text-lg font-semibold">
                                @if ($patient->blood_group)
                                    <span
                                        class="inline-block bg-white dark:bg-red-900 text-red-600 dark:text-red-200 font-bold px-3 py-1 rounded-full"
                                    >
                                        {{ $patient->blood_group->value() }}
                                    </span>
                                @else
                                    <span class="text-gray-300">No registrado</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex flex-col space-y-2">
                    <a
                        href="{{ route('pacientes.edit', $patient->id) }}"
                        class="bg-white dark:bg-zinc-800 text-teal-600 dark:text-teal-400 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 transition font-semibold flex items-center gap-2 justify-center"
                    >
                        <i class="fas fa-edit"></i>
                        Editar
                    </a>
                    <a
                        href="{{ route('pacientes.index') }}"
                        class="bg-teal-500 dark:bg-teal-700 text-white px-4 py-2 rounded-lg hover:bg-teal-700 dark:hover:bg-teal-600 transition font-semibold flex items-center gap-2 justify-center"
                    >
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </a>

                    <form
                        action="{{ route('pacientes.destroy', $patient->id) }}"
                        method="POST"
                        class="inline"
                        onsubmit="return confirm('¿Está seguro de eliminar este paciente?');"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="w-full bg-red-600 dark:bg-red-700 text-white px-4 py-2 rounded-lg hover:bg-red-700 dark:hover:bg-red-600 transition font-semibold flex items-center gap-2 justify-center"
                        >
                            <i class="fas fa-trash"></i>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Grid de contenido principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contenido principal (2 columnas) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Datos de Nacimiento -->
                @if ($patient->birth_weight || $patient->birth_height || $patient->birth_head_circumference || $patient->birth_type || $patient->birth_place)
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6"
                    >
                        <h2 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-baby text-teal-600 dark:text-teal-400"></i>
                            Datos de Nacimiento
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @if ($patient->birth_weight)
                                <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
                                    <p class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase">Peso</p>
                                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        {{ is_object($patient->birth_weight) ? $patient->birth_weight->value() : $patient->birth_weight }}
                                        kg
                                    </p>
                                </div>
                            @endif

                            @if ($patient->birth_height)
                                <div class="bg-green-50 dark:bg-green-900/30 p-3 rounded-lg">
                                    <p class="text-xs font-bold text-green-700 dark:text-green-400 uppercase">Talla</p>
                                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        {{ is_object($patient->birth_height) ? $patient->birth_height->value() : $patient->birth_height }}
                                        cm
                                    </p>
                                </div>
                            @endif

                            @if ($patient->birth_head_circumference)
                                <div class="bg-purple-50 dark:bg-purple-900/30 p-3 rounded-lg">
                                    <p class="text-xs font-bold text-purple-700 dark:text-purple-400 uppercase">
                                        Perímetro Cefálico
                                    </p>
                                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        {{ is_object($patient->birth_head_circumference) ? $patient->birth_head_circumference->value() : $patient->birth_head_circumference }}
                                        cm
                                    </p>
                                </div>
                            @endif

                            @if ($patient->birth_type)
                                <div class="bg-yellow-50 dark:bg-yellow-900/30 p-3 rounded-lg col-span-2 md:col-span-1">
                                    <p class="text-xs font-bold text-yellow-700 dark:text-yellow-400 uppercase">
                                        Tipo de Parto
                                    </p>
                                    <p class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                        {{ is_object($patient->birth_type) ? $patient->birth_type->value() : $patient->birth_type }}
                                    </p>
                                </div>
                            @endif

                            @if ($patient->birth_place)
                                <div class="bg-indigo-50 dark:bg-indigo-900/30 p-3 rounded-lg col-span-2 md:col-span-1">
                                    <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase">
                                        Lugar de Nac.
                                    </p>
                                    <p class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $patient->birth_place }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Condiciones Médicas / Screening -->
                @if ($patient->medicalConditions->count() > 0)
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6"
                    >
                        <h2 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-stethoscope text-teal-600 dark:text-teal-400"></i>
                            Screening Médico
                        </h2>
                        <div class="space-y-3">
                            @foreach ($patient->medicalConditions as $condition)
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-800 rounded-lg"
                                >
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-check-circle text-gray-600 dark:text-gray-400"></i>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $condition->name }}
                                        </span>
                                    </div>
                                    <div>
                                        @php
                                            $status = $condition->pivot->status;
                                        @endphp

                                        @if ($status === 'Positive')
                                            <span
                                                class="bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 text-xs font-bold px-3 py-1 rounded-full"
                                            >
                                                POSITIVO
                                            </span>
                                        @elseif ($status === 'Negative')
                                            <span
                                                class="bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 text-xs font-bold px-3 py-1 rounded-full"
                                            >
                                                NEGATIVO
                                            </span>
                                        @else
                                            <span
                                                class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold px-3 py-1 rounded-full"
                                            >
                                                NO TESTADO
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($condition->pivot->notes)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 italic pl-6 pb-2">
                                        <strong>Notas:</strong>
                                        {{ $condition->pivot->notes }}
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Antecedentes Médicos -->
                @if ($patient->allergies || $patient->pathologies || $patient->surgeries)
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6"
                    >
                        <h2 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-history text-teal-600 dark:text-teal-400"></i>
                            Antecedentes Médicos
                        </h2>
                        <div class="space-y-4">
                            @if ($patient->allergies)
                                <div>
                                    <h3 class="font-semibold text-red-600 dark:text-red-400 mb-2 text-sm uppercase">
                                        ⚠️ Antecedentes Alérgicos
                                    </h3>
                                    <p
                                        class="text-gray-700 dark:text-gray-300 bg-red-50 dark:bg-red-900/30 p-3 rounded-lg"
                                    >
                                        {{ $patient->allergies }}
                                    </p>
                                </div>
                            @endif

                            @if ($patient->pathologies)
                                <div>
                                    <h3
                                        class="font-semibold text-yellow-600 dark:text-yellow-400 mb-2 text-sm uppercase"
                                    >
                                        🏥 Antecedentes Patológicos
                                    </h3>
                                    <p
                                        class="text-gray-700 dark:text-gray-300 bg-yellow-50 dark:bg-yellow-900/30 p-3 rounded-lg"
                                    >
                                        {{ $patient->pathologies }}
                                    </p>
                                </div>
                            @endif

                            @if ($patient->surgeries)
                                <div>
                                    <h3 class="font-semibold text-blue-600 dark:text-blue-400 mb-2 text-sm uppercase">
                                        🔪 Antecedentes Quirúrgicos
                                    </h3>
                                    <p
                                        class="text-gray-700 dark:text-gray-300 bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg"
                                    >
                                        {{ $patient->surgeries }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar derecho (1 columna) -->
            <div class="space-y-6">
                <!-- Información de Usuario/Contacto -->
                @if ($patient->user)
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6"
                    >
                        <h2 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-user text-teal-600 dark:text-teal-400"></i>
                            Usuario
                        </h2>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Email</p>
                                <p class="text-sm text-gray-800 dark:text-gray-200 truncate">
                                    {{ $patient->user->email }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">
                                    Nombre Usuario
                                </p>
                                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $patient->user->name }}</p>
                            </div>
                            @if ($patient->user->phone_number)
                                <div>
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Teléfono</p>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">
                                        {{ $patient->user->phone_number->value() }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Estadísticas rápidas -->
                <div
                    class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-xl border border-teal-100 dark:border-teal-800 p-6"
                >
                    <h2 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-teal-600 dark:text-teal-400"></i>
                        Resumen
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-2 bg-white dark:bg-zinc-800 rounded-lg">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Condiciones Médicas</span>
                            <span
                                class="bg-teal-100 dark:bg-teal-900/50 text-teal-800 dark:text-teal-200 font-bold px-3 py-1 rounded-full"
                            >
                                {{ $patient->medicalConditions->count() }}
                            </span>
                        </div>
                        @php
                            $positiveCount = $patient->medicalConditions->where('pivot.status', 'Positive')->count();
                            $negativeCount = $patient->medicalConditions->where('pivot.status', 'Negative')->count();
                            $notTestedCount = $patient->medicalConditions->where('pivot.status', 'Not tested')->count();
                        @endphp

                        @if ($positiveCount > 0)
                            <div
                                class="flex justify-between items-center p-2 bg-white dark:bg-zinc-800 rounded-lg text-red-600 dark:text-red-400"
                            >
                                <span class="text-sm">Positivos</span>
                                <span
                                    class="bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 font-bold px-3 py-1 rounded-full"
                                >
                                    {{ $positiveCount }}
                                </span>
                            </div>
                        @endif

                        @if ($negativeCount > 0)
                            <div
                                class="flex justify-between items-center p-2 bg-white dark:bg-zinc-800 rounded-lg text-green-600 dark:text-green-400"
                            >
                                <span class="text-sm">Negativos</span>
                                <span
                                    class="bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 font-bold px-3 py-1 rounded-full"
                                >
                                    {{ $negativeCount }}
                                </span>
                            </div>
                        @endif

                        @if ($notTestedCount > 0)
                            <div
                                class="flex justify-between items-center p-2 bg-white dark:bg-zinc-800 rounded-lg text-gray-600 dark:text-gray-400"
                            >
                                <span class="text-sm">No Testados</span>
                                <span
                                    class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold px-3 py-1 rounded-full"
                                >
                                    {{ $notTestedCount }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6"
                >
                    <h2 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                        <i class="fas fa-lightning-bolt text-teal-600 dark:text-teal-400"></i>
                        Acciones
                    </h2>
                    <div class="space-y-2">
                        <a
                            href="{{ route('pacientes.edit', $patient->id) }}"
                            class="w-full block bg-teal-600 dark:bg-teal-700 text-white px-4 py-2 rounded-lg hover:bg-teal-700 dark:hover:bg-teal-600 transition text-center font-semibold"
                        >
                            <i class="fas fa-edit"></i>
                            Editar Paciente
                        </a>
                        <button
                            type="button"
                            class="w-full block bg-blue-600 dark:bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition text-center font-semibold disabled opacity-50 cursor-not-allowed"
                            disabled
                            title="Funcionalidad próximas fases"
                        >
                            <i class="fas fa-plus"></i>
                            Nueva Consulta
                        </button>
                    </div>
                </div>

                <!-- Información de Sistema -->
                <div
                    class="bg-gray-50 dark:bg-zinc-800 rounded-xl border border-gray-100 dark:border-zinc-700 p-6 text-xs"
                >
                    <p class="text-gray-500 dark:text-gray-400 mb-1">
                        <strong>ID:</strong>
                        {{ $patient->id }}
                    </p>
                    <p class="text-gray-500 dark:text-gray-400 mb-1">
                        <strong>Creado:</strong>
                        {{ $patient->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="text-gray-500 dark:text-gray-400">
                        <strong>Actualizado:</strong>
                        {{ $patient->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Sección de Consultas (si existen) -->
        @if ($patient->consultations && $patient->consultations->count() > 0)
            <div class="mt-8">
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6"
                >
                    <h2 class="text-lg font-bold text-teal-700 dark:text-teal-400 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-medical text-teal-600 dark:text-teal-400"></i>
                        Últimas Consultas
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800 border-b dark:border-zinc-700">
                                <tr>
                                    <th class="text-left py-3 px-4 text-gray-700 dark:text-gray-300">Fecha</th>
                                    <th class="text-left py-3 px-4 text-gray-700 dark:text-gray-300">Tipo</th>
                                    <th class="text-left py-3 px-4 text-gray-700 dark:text-gray-300">Doctor</th>
                                    <th class="text-left py-3 px-4 text-gray-700 dark:text-gray-300">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patient->consultations->take(5) as $consultation)
                                    <tr class="border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $consultation->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="inline-block bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 text-xs font-bold px-2 py-1 rounded"
                                            >
                                                {{ $consultation->consultation_type->value() ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-gray-900 dark:text-gray-100">
                                            {{ $consultation->doctor->user->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <button
                                                class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 font-semibold text-xs disabled opacity-50"
                                                disabled
                                                title="Funcionalidad próximas fases"
                                            >
                                                Ver
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 px-4 text-center text-gray-400 dark:text-gray-500">
                                            No hay consultaciones registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
