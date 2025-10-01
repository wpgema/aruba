<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductStockRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'date'       => 'required|date',
            'stock'      => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib diisi.',
            'product_id.exists'   => 'Produk tidak ditemukan.',
            'date.required'       => 'Tanggal wajib diisi.',
            'date.date'           => 'Tanggal tidak valid.',
            'stock.required'      => 'Stok wajib diisi.',
            'stock.integer'       => 'Stok harus berupa bilangan bulat.',
            'stock.min'           => 'Stok tidak boleh kurang dari 0.',
        ];
    }
}
