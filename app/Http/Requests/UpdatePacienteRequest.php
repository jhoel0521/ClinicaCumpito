<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePacienteRequest extends FormRequest
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
            'heel_prick_done' => ['nullable', 'boolean'],
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
            'full_name.max' => 'El nombre no debe exceder 255 caracteres.',
            'date_of_birth.required' => 'La fecha de nacimiento es obligatoria.',
            'date_of_birth.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'date_of_birth.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.required' => 'El género es obligatorio.',
            'gender.in' => 'El género debe ser Masculino o Femenino.',
            'blood_group.max' => 'El grupo sanguíneo no debe exceder 30 caracteres.',
            'birth_weight.numeric' => 'El peso debe ser un número válido.',
            'birth_weight.min' => 'El peso debe ser mayor a 0.1 kg.',
            'birth_weight.max' => 'El peso no debe exceder 10 kg.',
            'birth_height.numeric' => 'La talla debe ser un número válido.',
            'birth_height.min' => 'La talla debe ser mayor a 0.1 cm.',
            'birth_height.max' => 'La talla no debe exceder 100 cm.',
            'birth_head_circumference.numeric' => 'El perímetro cefálico debe ser un número válido.',
            'birth_head_circumference.min' => 'El perímetro cefálico debe ser mayor a 0.1 cm.',
            'birth_head_circumference.max' => 'El perímetro cefálico no debe exceder 100 cm.',
            'birth_type.in' => 'El tipo de parto no es válido.',
            'birth_place.max' => 'El lugar de nacimiento no debe exceder 255 caracteres.',
            'allergies.max' => 'Los antecedentes alérgicos no deben exceder 1000 caracteres.',
            'pathologies.max' => 'Los antecedentes patológicos no deben exceder 1000 caracteres.',
            'surgeries.max' => 'Los antecedentes quirúrgicos no deben exceder 1000 caracteres.',
            'medical_conditions.array' => 'Las condiciones médicas deben ser un array válido.',
            'medical_conditions.*.condition_id.required' => 'El ID de la condición es obligatorio.',
            'medical_conditions.*.condition_id.uuid' => 'El ID de la condición debe ser un UUID válido.',
            'medical_conditions.*.condition_id.exists' => 'La condición médica seleccionada no existe.',
            'medical_conditions.*.status.in' => 'El estado debe ser Positive, Negative o Not tested.',
            'medical_conditions.*.notes.max' => 'Las notas no deben exceder 500 caracteres.',
        ];
    }
}
