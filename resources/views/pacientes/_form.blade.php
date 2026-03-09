{{--
    Partial compartido entre create y edit de paciente.
    Variables disponibles:
    - $patient  : Patient|null  (null en create, modelo en edit)
    - $conditions : Collection<MedicalCondition>
--}}
@php
    // Valores actuales (edit los toma del modelo, create de old() o vacío)
    $curGender = old('gender', $patient?->gender?->value() ?? '');
    $curBirthType = old('birth_type', $patient?->birth_type?->value() ?? '');

    // Grupo sanguíneo: determinar si el valor actual es estándar o personalizado
    $curBg = old('blood_group', $patient?->blood_group?->value() ?? '');
    $standardBgs = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
    $bgIsCustom = $curBg !== '' && ! in_array($curBg, $standardBgs);
    $bgOption = $bgIsCustom ? 'Otro' : $curBg; // qué pill marcar
    $bgOtherVal = $bgIsCustom ? $curBg : ''; // texto del campo Otro

    // Mapa de condiciones existentes (solo en edit)
    $medCondMap = $patient?->medicalConditions?->pluck('pivot.status', 'id')->all() ?? [];
@endphp

<!-- 1. Datos de Filiación -->
<div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800">
    <h2 class="text-lg font-semibold text-teal-600 dark:text-teal-400 mb-4 border-b dark:border-zinc-700 pb-2">
        1. Datos de Filiación
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Nombre --}}
        <div class="col-span-2">
            <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nombre Completo
                <span class="text-red-500">*</span>
            </label>
            <input
                id="full_name"
                type="text"
                name="full_name"
                required
                placeholder="Ej: Aitana Aguilar"
                value="{{ old('full_name', $patient?->full_name) }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500 @error('full_name') border-red-500 dark:border-red-600 @enderror"
            />
            @error('full_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Fecha de Nacimiento --}}
        <div>
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Fecha de Nacimiento
            </label>
            <input
                id="date_of_birth"
                type="date"
                name="date_of_birth"
                value="{{ old('date_of_birth', $patient?->date_of_birth?->format('Y-m-d')) }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500 @error('date_of_birth') border-red-500 dark:border-red-600 @enderror"
            />
            @error('date_of_birth')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Género --}}
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
                        {{ $curGender === 'F' ? 'checked' : '' }}
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
                        {{ $curGender === 'M' ? 'checked' : '' }}
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

<!-- 2. Datos al Nacer -->
<div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800">
    <h2 class="text-lg font-semibold text-teal-600 dark:text-teal-400 mb-4 border-b dark:border-zinc-700 pb-2">
        2. Datos al Nacer (Histórico)
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <div>
            <label for="birth_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Peso al nacer (kg)
            </label>
            <input
                id="birth_weight"
                type="number"
                step="0.01"
                name="birth_weight"
                placeholder="Ej: 3.200"
                value="{{ old('birth_weight', $patient?->birth_weight) }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
            />
            @error('birth_weight')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="birth_height" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Talla al nacer (cm)
            </label>
            <input
                id="birth_height"
                type="number"
                step="0.1"
                name="birth_height"
                placeholder="Ej: 50"
                value="{{ old('birth_height', $patient?->birth_height) }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
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
                value="{{ old('birth_head_circumference', $patient?->birth_head_circumference) }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
            />
            @error('birth_head_circumference')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Tipo de Parto --}}
        <div>
            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Parto</span>
            <div class="flex gap-2">
                <label for="birth-type-normal" class="relative flex-1 cursor-pointer">
                    <input
                        type="radio"
                        id="birth-type-normal"
                        name="birth_type"
                        value="Normal"
                        class="peer sr-only"
                        {{ $curBirthType === 'Normal' ? 'checked' : '' }}
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
                        {{ $curBirthType === 'Cesarean' ? 'checked' : '' }}
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

        {{-- Lugar de Nacimiento --}}
        <div>
            <label for="birth_place" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Lugar de Nacimiento
            </label>
            <input
                id="birth_place"
                type="text"
                name="birth_place"
                placeholder="Clínica / Hospital"
                value="{{ old('birth_place', $patient?->birth_place) }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
            />
        </div>
    </div>
</div>

<!-- 3. Antecedentes y Screening -->
<div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-zinc-800">
    <h2 class="text-lg font-semibold text-teal-600 dark:text-teal-400 mb-4 border-b dark:border-zinc-700 pb-2">
        3. Antecedentes y Screening
    </h2>

    {{-- Grupo Sanguíneo --}}
    <div class="mb-6">
        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Grupo Sanguíneo
            <span class="text-gray-400 dark:text-zinc-500 font-normal">(opcional)</span>
        </span>
        <div class="flex flex-wrap gap-2">
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
                        {{ $bgOption === $group ? 'checked' : '' }}
                    />
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-full border-2 text-sm font-semibold transition-all border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/30 peer-checked:text-teal-700 dark:peer-checked:text-teal-300 hover:border-teal-300 dark:hover:border-teal-600"
                    >
                        {{ $group }}
                    </span>
                </label>
            @endforeach
        </div>
        <div id="blood-group-otro-field" class="{{ $bgIsCustom ? '' : 'hidden' }} mt-3">
            <input
                type="text"
                name="blood_group_other"
                id="blood_group_other"
                placeholder="Especificar grupo sanguíneo..."
                value="{{ $bgOtherVal }}"
                class="w-full sm:w-64 px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500"
            />
        </div>
        <input type="hidden" name="blood_group" id="blood-group-value" value="{{ $curBg }}" />
    </div>

    {{-- Condiciones Médicas --}}
    <div class="mb-6">
        <span class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
            Condiciones Médicas Detectadas
        </span>
        <div class="space-y-2">
            @foreach ($conditions as $condition)
                @php
                    $currentStatus = old(
                        'medical_conditions.' . $condition->id . '.status',
                        $medCondMap[$condition->id] ?? '',
                    );
                @endphp

                <div
                    class="flex items-center justify-between p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 bg-white dark:bg-zinc-900"
                >
                    <span class="text-sm text-gray-900 dark:text-gray-100 font-medium">{{ $condition->name }}</span>
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
                            <option value="Positive" @selected($currentStatus === 'Positive')>Positivo</option>
                            <option value="Negative" @selected($currentStatus === 'Negative')>Negativo</option>
                            <option value="Not tested" @selected($currentStatus === 'Not tested')>No se hizo</option>
                        </select>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Antecedentes --}}
    <div class="space-y-4">
        <div>
            <label for="allergies" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Antecedentes Alérgicos
            </label>
            <textarea
                id="allergies"
                name="allergies"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                placeholder="Medicamentos, alimentos..."
            >
{{ old('allergies', $patient?->allergies) }}</textarea
            >
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="pathologies" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Antecedentes Patológicos
                </label>
                <textarea
                    id="pathologies"
                    name="pathologies"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                >
{{ old('pathologies', $patient?->pathologies) }}</textarea
                >
            </div>
            <div>
                <label for="surgeries" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Antecedentes Quirúrgicos
                </label>
                <textarea
                    id="surgeries"
                    name="surgeries"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg shadow-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-teal-500 focus:border-teal-500"
                >
{{ old('surgeries', $patient?->surgeries) }}</textarea
                >
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pills = document.querySelectorAll('input[name="blood_group_option"]');
        const otroField = document.getElementById('blood-group-otro-field');
        const otroInput = document.getElementById('blood_group_other');
        const hidden = document.getElementById('blood-group-value');

        function sync() {
            const checked = document.querySelector('input[name="blood_group_option"]:checked');
            if (!checked) {
                hidden.value = '';
                return;
            }
            if (checked.value === 'Otro') {
                otroField.classList.remove('hidden');
                hidden.value = otroInput ? otroInput.value : '';
            } else {
                otroField.classList.add('hidden');
                hidden.value = checked.value;
            }
        }

        pills.forEach((p) => p.addEventListener('change', sync));
        if (otroInput) otroInput.addEventListener('input', sync);
        sync();
    });
</script>
