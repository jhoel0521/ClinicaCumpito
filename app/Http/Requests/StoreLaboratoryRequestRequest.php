<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaboratoryRequestRequest extends FormRequest
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
            'source_template_id' => ['nullable', 'uuid', 'exists:laboratory_templates,id'],
            'observations' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
