<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionItemRequest extends FormRequest
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
            'source_template_item_id' => ['nullable', 'uuid', 'exists:prescription_template_items,id'],
            'medication_name' => ['required', 'string', 'max:255'],
            'dose' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'duration' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
