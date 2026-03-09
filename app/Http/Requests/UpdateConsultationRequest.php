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
            'consultation_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,saved,finalized'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consultation_date.required' => 'La fecha de consulta es obligatoria.',
            'consultation_date.date' => 'La fecha de consulta debe ser válida.',
            'status.required' => 'El estado de la consulta es obligatorio.',
            'status.in' => 'El estado debe ser draft, saved o finalized.',
        ];
    }
}
