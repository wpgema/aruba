<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price_buy' => 'nullable|numeric|min:0',
            'price_sale' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048', 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Nama produk tidak boleh lebih dari 255 karakter.',
            'slug.required' => 'Kode Produk wajib diisi.',
            'slug.string' => 'Kode Produk harus berupa teks.',
            'slug.max' => 'Kode Produk tidak boleh lebih dari 255 karakter.',
            'slug.unique' => 'Kode Produk sudah digunakan, silakan gunakan Kode Produk lain.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'price_buy.numeric' => 'Harga beli harus berupa angka.',
            'price_buy.min' => 'Harga beli tidak boleh kurang dari 0.',
            'price_sale.required' => 'Harga jual wajib diisi.',
            'price_sale.numeric' => 'Harga jual harus berupa angka.',
            'price_sale.min' => 'Harga jual tidak boleh kurang dari 0.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
            'is_active.boolean' => 'Status aktif harus berupa nilai boolean.',
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
    }
}
