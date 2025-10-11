<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCapitalkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authenticated users to create capitals — adjust as needed for your auth logic
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
            'type' => 'required|in:harian,bulanan,tahunan',
            'date' => 'required|date',
            'amount' => 'required|integer|min:0',
            'description' => 'required|string',
        ];
    }
    
    /**
     * Custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Tipe modal wajib diisi.',
            'type.in' => 'Tipe modal harus salah satu dari: harian, bulanan, tahunan.',
            'date.required' => 'Tanggal modal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'amount.required' => 'Jumlah modal wajib diisi.',
            'amount.integer' => 'Jumlah modal harus berupa angka.',
            'amount.min' => 'Jumlah modal tidak boleh kurang dari 0.',
            'description.required' => 'Deskripsi harus diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
