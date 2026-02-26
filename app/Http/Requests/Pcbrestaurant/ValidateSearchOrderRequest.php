<?php

namespace App\Http\Requests\Pcbrestaurant;

use Illuminate\Foundation\Http\FormRequest;

class ValidateSearchOrderRequest extends FormRequest
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
            'ticketFolio' => 'nullable|integer|min:1',
            'totalAmount' => 'nullable|numeric',
            'date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'ticketFolio.required' => 'El campo de "Folio del Ticket" es obligatorio.',
            'ticketFolio.integer' => 'El campo de "Folio del Ticket" debe ser un número valido.',
            'totalAmount.numeric' => 'El "Imprte Total" debe ser un número válido.',
            'date.date' => 'La fecha debe ser una fecha válida.',
        ];
    }
}
