<x-layouts::app :title="__('Editar Paciente')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-teal-700">Editar Paciente</h1>
            <p class="text-gray-500 text-sm mt-2">{{ $patient->full_name }}</p>
        </div>
        <a href="{{ route('pacientes.show', $patient->id) }}"
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <form action="{{ route('pacientes.update', $patient->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Datos de Filiación -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-teal-600 mb-4 border-b pb-2">1. Datos de Filiación</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                    <input type="text" name="full_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500"
                        value="{{ old('full_name', $patient->full_name) }}">
                    @error('full_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento</label>
                    <input type="date" name="date_of_birth"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500"
                        value="{{ old('date_of_birth', $patient->date_of_birth->format('Y-m-d')) }}">
                    @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                    <select name="gender"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="F" @selected($patient->gender->value() === 'F')>Femenino</option>
                        <option value="M" @selected($patient->gender->value() === 'M')>Masculino</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Datos de Nacimiento -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-teal-600 mb-4 border-b pb-2">2. Datos al Nacer (Histórico)</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Peso al nacer (kg)</label>
                    <input type="number" step="0.01" name="birth_weight"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500"
                        value="{{ old('birth_weight', is_object($patient->birth_weight) ? $patient->birth_weight->value() : $patient->birth_weight) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Talla al nacer (cm)</label>
                    <input type="number" step="0.1" name="birth_height"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500"
                        value="{{ old('birth_height', is_object($patient->birth_height) ? $patient->birth_height->value() : $patient->birth_height) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Perímetro Cefálico (cm)</label>
                    <input type="number" step="0.1" name="birth_head_circumference"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500"
                        value="{{ old('birth_head_circumference', is_object($patient->birth_head_circumference) ? $patient->birth_head_circumference->value() : $patient->birth_head_circumference) }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Parto</label>
                    <select name="birth_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="">-- Seleccione --</option>
                        <option value="Normal" @selected(old('birth_type', $patient->birth_type?->value()) === 'Normal')>Parto Normal</option>
                        <option value="Cesarean" @selected(old('birth_type', $patient->birth_type?->value()) === 'Cesarean')>Cesárea</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lugar de Nacimiento</label>
                    <input type="text" name="birth_place"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500"
                        value="{{ old('birth_place', $patient->birth_place) }}">
                </div>
            </div>
        </div>

        <!-- 3. Antecedentes y Screening -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-teal-600 mb-4 border-b pb-2">3. Antecedentes y Screening</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-teal-50 p-3 rounded-lg border border-teal-100">
                    <label class="block text-xs font-bold text-teal-800 uppercase mb-2">Grupo Sanguíneo</label>
                    <select name="blood_group"
                        class="w-full bg-white border border-gray-300 rounded shadow-sm text-sm px-2 py-1">
                        <option value="">-- Seleccione --</option>
                        @foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $group)
                            <option value="{{ $group }}" @selected(old('blood_group', $patient->blood_group?->value()) === $group)>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Condiciones Médicas -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-3">Condiciones Médicas</label>
                <div class="space-y-2">
                    @php
                        $patientConditions = $patient->medicalConditions()->pluck('medical_condition_id')->toArray();
                    @endphp
                    @foreach ($conditions as $condition)
                        <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <input type="checkbox" id="condition_{{ $condition->id }}"
                                value="{{ $condition->id }}" 
                                @checked(in_array($condition->id, $patientConditions))
                                class="h-4 w-4 text-teal-600 border-gray-300 rounded"
                                onchange="toggleConditionStatus(this, '{{ $condition->id }}')">
                            <label for="condition_{{ $condition->id }}"
                                class="ml-2 block text-sm text-gray-900 font-medium flex-grow">
                                {{ $condition->name }}
                            </label>
                            @php
                                $pivotStatus = $patient->medicalConditions()
                                    ->where('medical_condition_id', $condition->id)
                                    ->first()
                                    ?->pivot
                                    ?->status;
                            @endphp
                            <select name="medical_conditions[{{ $condition->id }}][status]"
                                id="status_{{ $condition->id }}"
                                style="display: {{ in_array($condition->id, $patientConditions) ? 'inline-block' : 'none' }};"
                                class="px-2 py-1 border border-gray-300 rounded text-sm">
                                <option value="Positive" @selected($pivotStatus === 'Positive')>Positivo</option>
                                <option value="Negative" @selected($pivotStatus === 'Negative')>Negativo</option>
                                <option value="Not tested" @selected($pivotStatus === 'Not tested')>No testado</option>
                            </select>
                            @if (in_array($condition->id, $patientConditions))
                                <input type="hidden" name="medical_conditions[{{ $condition->id }}][condition_id]"
                                    value="{{ $condition->id }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Text Areas -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Antecedentes Alérgicos</label>
                    <textarea name="allergies" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">{{ old('allergies', $patient->allergies) }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Antecedentes Patológicos</label>
                        <textarea name="pathologies" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">{{ old('pathologies', $patient->pathologies) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Antecedentes Quirúrgicos</label>
                        <textarea name="surgeries" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500">{{ old('surgeries', $patient->surgeries) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-end space-x-4 pt-4">
            <a href="{{ route('pacientes.show', $patient->id) }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                <i class="fas fa-save mr-2"></i> Actualizar Paciente
            </button>
        </div>
    </form>
</div>

<script>
    function toggleConditionStatus(checkbox, conditionId) {
        const statusSelect = document.getElementById(`status_${conditionId}`);
        let hiddenInput = document.querySelector(`input[name="medical_conditions[${conditionId}][condition_id]"]`);

        if (checkbox.checked) {
            statusSelect.style.display = 'inline-block';
            if (!hiddenInput) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `medical_conditions[${conditionId}][condition_id]`;
                input.value = conditionId;
                checkbox.parentElement.appendChild(input);
            }
        } else {
            statusSelect.style.display = 'none';
            if (hiddenInput) hiddenInput.remove();
        }
    }
</script>
    </div>
</x-layouts::app>
