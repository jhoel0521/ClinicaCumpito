<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Doctor') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $doctorId = $this->user()?->doctor?->id;

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_number' => [
                'required',
                'string',
                'min:5',
                'max:50',
                'regex:/^[A-Za-z0-9\-\/]+$/',
                'unique:doctors,license_number,'.$doctorId,
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'license_number.regex' => 'El número de licencia solo puede contener letras, números, guiones y barras.',
            'license_number.unique' => 'Este número de licencia ya está registrado.',
        ];
    }
}
