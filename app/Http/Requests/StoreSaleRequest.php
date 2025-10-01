<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
            'date' => 'required|date',
            'table_number' => 'nullable|string|max:50',
            'discount' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,card,qris',
            'paid_amount' => 'required|integer|min:0',
            'status' => 'nullable|in:paid,unpaid,cancelled',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|integer|min:0',
            'products.*.note' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal penjualan wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'paid_amount.required' => 'Jumlah bayar wajib diisi.',
            'products.required' => 'Minimal harus ada 1 produk.',
            'products.*.product_id.required' => 'ID produk wajib diisi.',
            'products.*.product_id.exists' => 'Produk tidak ditemukan.',
            'products.*.quantity.required' => 'Kuantitas produk wajib diisi.',
            'products.*.quantity.min' => 'Kuantitas minimal 1.',
            'products.*.price.required' => 'Harga produk wajib diisi.',
        ];
    }
}