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
                        <label
                            for="date_of_birth"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Fecha de Nacimiento
                        </label>
                        <input
                            id="date_of_birth"
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
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Género</span>
                        <div class="flex gap-2">
                            <label for="gender-f" class="relative flex-1 cursor-pointer">
                                <input
                                    type="radio"
                                    id="gender-f"
                                    name="gender"
                                    value="F"
                                    class="peer sr-only"
                                    {{ old('gender') === 'F' ? 'checked' : '' }}
                                />
                                <span
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 peer-checked:border-pink-500 peer-checked:bg-pink-50 dark:peer-checked:bg-pink-900/30 peer-checked:text-pink-700 dark:peer-checked:text-pink-300 hover:border-pink-300 hover:bg-pink-50 dark:hover:bg-zinc-700"
                                >
                                    <i class="fas fa-venus"></i>
                                    Femenino
                                </span>
                            </label>
                            <label for="gender-m" class="relative flex-1 cursor-pointer">
                                <input
                                    type="radio"
                                    id="gender-m"
                                    name="gender"
                                    value="M"
                                    class="peer sr-only"
                                    {{ old('gender') === 'M' ? 'checked' : '' }}
                                />
                                <span
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 peer-checked:border-sky-500 peer-checked:bg-sky-50 dark:peer-checked:bg-sky-900/30 peer-checked:text-sky-700 dark:peer-checked:text-sky-300 hover:border-sky-300 hover:bg-sky-50 dark:hover:bg-zinc-700"
                                >
                                    <i class="fas fa-mars"></i>
                                    Masculino
                                </span>
                            </label>
                        </div>
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
                        <label
                            for="birth_weight"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Peso al nacer (kg)
                        </label>
                        <input
                            id="birth_weight"
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
                        <label
                            for="birth_height"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Talla al nacer (cm)
                        </label>
                        <input
                            id="birth_height"
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
                        <label
                            for="birth_head_circumference"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Perímetro Cefálico (cm)
                        </label>
                        <input
                            id="birth_head_circumference"
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
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Parto
                        </span>
                        <div class="flex gap-2">
                            <label for="birth-type-normal" class="relative flex-1 cursor-pointer">
                                <input
                                    type="radio"
                                    id="birth-type-normal"
                                    name="birth_type"
                                    value="Normal"
                                    class="peer sr-only"
                                    {{ old('birth_type') === 'Normal' ? 'checked' : '' }}
                                />
                                <span
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/30 peer-checked:text-teal-700 dark:peer-checked:text-teal-300 hover:border-teal-300 hover:bg-teal-50 dark:hover:bg-zinc-700"
                                >
                                    <i class="fas fa-baby"></i>
                                    Parto Normal
                                </span>
                            </label>
                            <label for="birth-type-cesarean" class="relative flex-1 cursor-pointer">
                                <input
                                    type="radio"
                                    id="birth-type-cesarean"
                                    name="birth_type"
                                    value="Cesarean"
                                    class="peer sr-only"
                                    {{ old('birth_type') === 'Cesarean' ? 'checked' : '' }}
                                />
                                <span
                                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/30 peer-checked:text-teal-700 dark:peer-checked:text-teal-300 hover:border-teal-300 hover:bg-teal-50 dark:hover:bg-zinc-700"
                                >
                                    <i class="fas fa-procedures"></i>
                                    Cesárea
                                </span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label
                            for="birth_place"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Lugar de Nacimiento
                        </label>
                        <input
                            id="birth_place"
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

                <div class="mb-6">
                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Grupo Sanguíneo
                        <span class="text-gray-400 dark:text-zinc-500 font-normal">(opcional)</span>
                    </span>
                    <div class="flex flex-wrap gap-2" id="blood-group-pills">
                        @foreach (['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'Otro'] as $group)
                            @php
                                $bgId = 'blood-group-' . $loop->index;
                            @endphp

                            <label for="{{ $bgId }}" class="cursor-pointer">
                                <input
                                    type="radio"
                                    id="{{ $bgId }}"
                                    name="blood_group_option"
                                    value="{{ $group }}"
                                    class="peer sr-only"
                                    {{ old('blood_group', old('blood_group_option')) === $group ? 'checked' : '' }}
                                />
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full border-2 text-sm font-semibold transition-all border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/30 peer-checked:text-teal-700 dark:peer-checked:text-teal-300 hover:border-teal-300 dark:hover:border-teal-600"
                                >
                                    {{ $group }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <!-- Campo Otro (oculto por defecto) -->
                    <div
                        id="blood-group-otro-field"
                        class="{{ old('blood_group_option') === 'Otro' ? '' : 'hidden' }} mt-3"
                    >
                        <input
                            type="text"
                            name="blood_group_other"
                            placeholder="Especificar grupo sanguíneo..."
                            value="{{ old('blood_group_other') }}"
                            class="w-full sm:w-64 px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500"
                        />
                    </div>
                    <!-- Input hidden que se envía al backend -->
                    <input type="hidden" name="blood_group" id="blood-group-value" value="{{ old('blood_group') }}" />
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pills = document.querySelectorAll('input[name="blood_group_option"]');
            const otroField = document.getElementById('blood-group-otro-field');
            const otroInput = otroField ? otroField.querySelector('input[name="blood_group_other"]') : null;
            const hiddenInput = document.getElementById('blood-group-value');

            function syncValue() {
                const checked = document.querySelector('input[name="blood_group_option"]:checked');
                if (!checked) {
                    hiddenInput.value = '';
                    return;
                }
                if (checked.value === 'Otro') {
                    otroField.classList.remove('hidden');
                    hiddenInput.value = otroInput ? otroInput.value : '';
                } else {
                    otroField.classList.add('hidden');
                    hiddenInput.value = checked.value;
                }
            }

            pills.forEach((p) => p.addEventListener('change', syncValue));
            if (otroInput) otroInput.addEventListener('input', syncValue);
            syncValue();
        });
    </script>
</x-layouts::app>
