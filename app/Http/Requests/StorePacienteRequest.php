<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:M,F'],
            'blood_group' => ['nullable', 'string', 'max:30'],
            'birth_weight' => ['nullable', 'numeric', 'min:0.1', 'max:10'],
            'birth_height' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            'birth_head_circumference' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            'birth_type' => ['nullable', 'in:Normal,Cesarean'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'pathologies' => ['nullable', 'string', 'max:1000'],
            'surgeries' => ['nullable', 'string', 'max:1000'],
            'medical_conditions' => ['nullable', 'array'],
            'medical_conditions.*.condition_id' => ['required', 'uuid', 'exists:medical_conditions,id'],
            'medical_conditions.*.status' => ['nullable', 'in:Positive,Negative,Not tested'],
            'medical_conditions.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'El nombre del paciente es obligatorio.',
            'date_of_birth.required' => 'La fecha de nacimiento es obligatoria.',
            'date_of_birth.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.required' => 'El género es obligatorio.',
            'gender.in' => 'El género debe ser Masculino o Femenino.',
        ];
    }
}
