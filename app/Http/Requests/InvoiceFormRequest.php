<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'max:255'],
            'date' => 'required|date_format:Y-m-d',
            'recipient' => 'nullable|string|max:255',
            'services' => 'required|array|min:1',
            'services.*.description' => 'required|string|max:255',
            'services.*.value' => 'required|array',
            'services.*.items' => 'required|integer|min:1',
        ];
    }
}
