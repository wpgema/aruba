<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleDetailRequest extends FormRequest
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
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sale_id.required' => 'ID penjualan wajib diisi.',
            'sale_id.exists' => 'Penjualan tidak ditemukan.',
            'product_id.required' => 'ID produk wajib diisi.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'quantity.required' => 'Kuantitas wajib diisi.',
            'quantity.min' => 'Kuantitas minimal 1.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
        ];
    }
}