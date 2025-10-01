<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
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
            'date' => 'sometimes|date',
            'table_number' => 'sometimes|nullable|string|max:50',
            'discount' => 'sometimes|nullable|integer|min:0',
            'payment_method' => 'sometimes|in:cash,card,qris',
            'paid_amount' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:paid,unpaid,cancelled',
            'products' => 'sometimes|array|min:1',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'required_with:products|integer|min:1',
            'products.*.price' => 'required_with:products|integer|min:0',
            'products.*.note' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.date' => 'Format tanggal tidak valid.',
            'payment_method.in' => 'Metode pembayaran harus cash, card, atau qris.',
            'products.min' => 'Minimal harus ada 1 produk.',
            'products.*.product_id.exists' => 'Produk tidak ditemukan.',
            'products.*.quantity.min' => 'Kuantitas minimal 1.',
            'products.*.price.min' => 'Harga tidak boleh negatif.',
        ];
    }
}