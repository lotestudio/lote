<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'address_1' => ['required', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'number' => ['required', 'numeric:'],
            'vat' => ['nullable', 'string', 'max:255'],
            'mol' => ['nullable', 'string', 'max:255'],
        ];
    }
}
