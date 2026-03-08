<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVitalSignRequest extends FormRequest
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
            'weight' => ['nullable', 'numeric', 'min:0.1', 'max:300'],
            'height' => ['nullable', 'numeric', 'min:0.1', 'max:250'],
            'head_circumference' => ['nullable', 'numeric', 'min:20', 'max:80'],
            'temperature' => ['nullable', 'numeric', 'min:35', 'max:42'],
        ];
    }
}
