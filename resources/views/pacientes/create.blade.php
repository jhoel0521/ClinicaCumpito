<x-layouts::app :title="__('Crear Paciente')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-teal-700 dark:text-teal-400">Nuevo Paciente</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                    Ingrese los datos base para iniciar el historial.
                </p>
            </div>
            <a
                href="{{ route('pacientes.index') }}"
                class="bg-gray-200 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-zinc-700 transition flex items-center gap-2"
            >
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>

        <!-- Formulario -->
        <form action="{{ route('pacientes.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- 1. Datos de Filiación -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800">
                <h2
                    class="text-lg font-semibold text-teal-600 dark:text-teal-400 mb-4 border-b dark:border-zinc-700 pb-2"
                >
                    1. Datos de Filiación
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <x-ui.input
                            name="full_name"
                            label="Nombre Completo"
                            placeholder="Ej: Aitana Aguilar"
                            :required="true"
                            class="@error('full_name') border-red-500 dark:border-red-600 @enderror"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha de Nacimiento
                        </label>
                        <input
                            type="date"
                            name="date_of_birth"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500 @error('date_of_birth') border-red-500 dark:border-red-600 @enderror"
                            value="{{ old('date_of_birth') }}"
                        />
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Género</label>
                        <select
                            name="gender"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500 @error('gender') border-red-500 dark:border-red-600 @enderror"
                        >
                            <option value="">-- Seleccione --</option>
                            <option value="F" @selected(old('gender') === 'F')>Femenino</option>
                            <option value="M" @selected(old('gender') === 'M')>Masculino</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 2. Datos de Nacimiento -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800">
                <h2
                    class="text-lg font-semibold text-teal-600 dark:text-teal-400 mb-4 border-b dark:border-zinc-700 pb-2"
                >
                    2. Datos al Nacer (Histórico)
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Peso al nacer (kg)
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            name="birth_weight"
                            placeholder="Ej: 3.200"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                            value="{{ old('birth_weight') }}"
                        />
                        @error('birth_weight')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Talla al nacer (cm)
                        </label>
                        <input
                            type="number"
                            step="0.1"
                            name="birth_height"
                            placeholder="Ej: 50"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                            value="{{ old('birth_height') }}"
                        />
                        @error('birth_height')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Perímetro Cefálico (cm)
                        </label>
                        <input
                            type="number"
                            step="0.1"
                            name="birth_head_circumference"
                            placeholder="Ej: 34"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                            value="{{ old('birth_head_circumference') }}"
                        />
                        @error('birth_head_circumference')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tipo de Parto
                        </label>
                        <select
                            name="birth_type"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                        >
                            <option value="">-- Seleccione --</option>
                            <option value="Normal" @selected(old('birth_type') === 'Normal')>Parto Normal</option>
                            <option value="Cesarean" @selected(old('birth_type') === 'Cesarean')>Cesárea</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Lugar de Nacimiento
                        </label>
                        <input
                            type="text"
                            name="birth_place"
                            placeholder="Clínica / Hospital"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                            value="{{ old('birth_place') }}"
                        />
                    </div>
                </div>
            </div>

            <!-- 3. Antecedentes y Screening -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800">
                <h2
                    class="text-lg font-semibold text-teal-600 dark:text-teal-400 mb-4 border-b dark:border-zinc-700 pb-2"
                >
                    3. Antecedentes y Screening
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div
                        class="bg-teal-50 dark:bg-teal-900/30 p-3 rounded-lg border border-teal-100 dark:border-teal-800"
                    >
                        <label class="block text-xs font-bold text-teal-800 dark:text-teal-400 uppercase mb-2">
                            Grupo Sanguíneo
                        </label>
                        <select
                            name="blood_group"
                            class="w-full bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded shadow-sm text-sm px-2 py-1 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                        >
                            <option value="">-- Seleccione --</option>
                            @foreach (['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $group)
                                <option value="{{ $group }}" @selected(old('blood_group') === $group)>
                                    {{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Condiciones Médicas -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        Condiciones Médicas Detectadas
                    </label>
                    <div class="space-y-2">
                        @foreach ($conditions as $condition)
                            <div
                                class="flex items-center justify-between p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 bg-white dark:bg-zinc-900"
                            >
                                <span class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    {{ $condition->name }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="hidden"
                                        name="medical_conditions[{{ $condition->id }}][condition_id]"
                                        value="{{ $condition->id }}"
                                    />
                                    <select
                                        name="medical_conditions[{{ $condition->id }}][status]"
                                        class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                                    >
                                        <option value="">— No evaluado —</option>
                                        <option
                                            value="Positive"
                                            @selected(old('medical_conditions.' . $condition->id . '.status') === 'Positive')
                                        >
                                            Positivo
                                        </option>
                                        <option
                                            value="Negative"
                                            @selected(old('medical_conditions.' . $condition->id . '.status') === 'Negative')
                                        >
                                            Negativo
                                        </option>
                                        <option
                                            value="Not tested"
                                            @selected(old('medical_conditions.' . $condition->id . '.status') === 'Not tested')
                                        >
                                            No se hizo
                                        </option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Text Areas -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Antecedentes Alérgicos
                        </label>
                        <textarea
                            name="allergies"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="Medicamentos, alimentos..."
                        >
{{ old('allergies') }}</textarea
                        >
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Antecedentes Patológicos
                            </label>
                            <textarea
                                name="pathologies"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                            >
{{ old('pathologies') }}</textarea
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Antecedentes Quirúrgicos
                            </label>
                            <textarea
                                name="surgeries"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                            >
{{ old('surgeries') }}</textarea
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-4 pt-4">
                <a
                    href="{{ route('pacientes.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-teal-600 dark:bg-teal-700 text-white rounded-lg hover:bg-teal-700 dark:hover:bg-teal-600 transition"
                >
                    <i class="fas fa-save mr-2"></i>
                    Crear Paciente
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
