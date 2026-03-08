<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
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
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'doctor_id' => ['required', 'uuid', 'exists:doctors,id'],
            'type' => ['required', 'in:digital,manual'],
            'status' => ['required', 'in:draft,saved,finalized'],
            'consultation_date' => ['required', 'date'],
            'scanned_file_path' => ['nullable', 'string', 'max:255'],
            'pending_transcription' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'El paciente es obligatorio.',
            'patient_id.exists' => 'El paciente seleccionado no existe.',
            'doctor_id.required' => 'El doctor es obligatorio.',
            'doctor_id.exists' => 'El doctor seleccionado no existe.',
            'type.required' => 'El tipo de consulta es obligatorio.',
            'type.in' => 'El tipo de consulta debe ser digital o manual.',
            'status.required' => 'El estado de la consulta es obligatorio.',
            'status.in' => 'El estado debe ser draft, saved o finalized.',
            'consultation_date.required' => 'La fecha de consulta es obligatoria.',
            'consultation_date.date' => 'La fecha de consulta debe ser válida.',
        ];
    }
}
