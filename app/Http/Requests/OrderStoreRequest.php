<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id,active,1', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Tablica items jest wymagana.',
            'items.min' => 'Lista itemów nie może być pusta.',
            'items.*.product_id.required' => 'Brak produktu dla jednego z itemów.',
            'items.*.product_id.exists' => 'Produkt nie istnieje lub nie jest aktywny.',
            'items.*.product_id.distinct' => 'Produkt o tym ID pojawia się więcej niż raz. Każdy produkt powinien być unikalny.',
            'items.*.quantity.required' => 'Ilość jest wymagana dla każdego itemu.',
            'items.*.quantity.min' => 'Ilość musi być większa niż 0.',
        ];
    }
}
