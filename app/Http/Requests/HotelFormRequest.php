<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelFormRequest extends FormRequest
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
            'ticketFolio' => 'required|string|min:1|max:50',
            'checkOut' => 'nullable|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'ticketFolio.required' => 'El folio del ticket es obligatorio.',
            'ticketFolio.string' => 'El folio del ticket debe ser una cadena de texto.',
            'ticketFolio.min' => 'El folio del ticket debe tener al menos :min caracteres.',
            'ticketFolio.max' => 'El folio del ticket no debe exceder de :max caracteres.',
            'checkOut.date_format' => 'La fecha de check-out debe tener el formato YYYY-MM-DD.',
        ];
    }
}
