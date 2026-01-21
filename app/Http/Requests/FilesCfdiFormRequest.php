<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilesCfdiFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cfdiData' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'cfdiData.required' => 'Los datos del CFDI son obligatorios.',
            'cfdiData.array' => 'Los datos del CFDI deben ser un arreglo.',
        ];
    }
}
