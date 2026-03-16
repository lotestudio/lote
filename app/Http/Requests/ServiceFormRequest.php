<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'max:255'],
            'description' => 'required|string|max:255',
            'value' => 'required|numeric',
            'items' => 'required|integer',
        ];
    }
}
