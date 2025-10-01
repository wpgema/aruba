<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
        $employeeId = $this->route('employee') ? $this->route('employee')->id : $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email,' . $employeeId . '|max:255',
            'phone' => 'nullable|string|max:20',
            'username' => 'required|string|unique:employees,username,' . $employeeId . '|max:255|min:3',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,kasir,barista,manager',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama karyawan harus diisi.',
            'name.string' => 'Nama karyawan harus berupa teks.',
            'name.max' => 'Nama karyawan maksimal 255 karakter.',
            
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan karyawan lain.',
            'email.max' => 'Email maksimal 255 karakter.',
            
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            
            'username.required' => 'Username harus diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.unique' => 'Username sudah digunakan karyawan lain.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.min' => 'Username minimal 3 karakter.',
            
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            
            'role.required' => 'Role karyawan harus dipilih.',
            'role.in' => 'Role yang dipilih tidak valid. Pilih salah satu: admin, kasir, barista, atau manager.',
            
            'is_active.boolean' => 'Status aktif harus berupa true atau false.'
        ];
    }
}
