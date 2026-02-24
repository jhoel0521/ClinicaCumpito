<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientVaccineRequest extends FormRequest
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
            'vaccine_id' => ['required', 'uuid', 'exists:vaccines,id'],
            'applied_at' => ['required', 'date'],
            'applied_by_doctor_id' => ['nullable', 'uuid', 'exists:doctors,id'],
            'application_site' => ['nullable', 'string', 'max:255'],
            'dose_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
