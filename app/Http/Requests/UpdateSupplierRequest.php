<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
        $supplierId = $this->route('supplier') ? $this->route('supplier')->id : null;

        return [
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('suppliers','slug')->ignore($supplierId),],
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($supplierId),],
            'address' => 'string|max:255',
            'phone' => 'string|max:255',
            'email' => 'string|email|max:255',
            'city' => 'string|max:255',
            'province' => 'string|max:255',
            'postal_code' => 'string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama supplier wajib diisi.',
            'name.max' => 'Nama supplier maksimal 255 karakter.',
            'name.unique' => 'Nama supplier telah digunakan.',
            'slug.unique' => 'Slug supplier sudah digunakan.',
            'slug.max' => 'Slug supplier maksimal 255 karakter.',
            'address.max' => 'Alamat supplier maksimal 255 karakter.',
            'phone.max' => 'Telepon supplier maksimal 255 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email supplier maksimal 255 karakter.',
            'city.max' => 'Kota supplier maksimal 255 karakter.',
            'province.max' => 'Provinsi supplier maksimal 255 karakter.',
            'postal_code.max' => 'Kode pos supplier maksimal 255 karakter.',
        ];
    }
}
