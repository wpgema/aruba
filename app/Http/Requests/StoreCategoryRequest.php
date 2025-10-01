<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
    public function rules()
    {
        return [
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'slug.max' => 'Slug kategori maksimal 255 karakter.',
        ];
    }
}
