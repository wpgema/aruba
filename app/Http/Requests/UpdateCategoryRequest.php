<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        return [
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories','slug')->ignore($categoryId),],
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($categoryId),],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'name.unique' => 'Nama kategori telah digunakan.',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'slug.max' => 'Slug kategori maksimal 255 karakter.',
        ];
    }
}
