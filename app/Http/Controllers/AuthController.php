<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Find employee by username
        $employee = Employee::where('username', $credentials['username'])->first();

        // Verify password using Hash::check
        if (!$employee || !Hash::check($credentials['password'], $employee->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        // Check if employee is active
        if (!$employee->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan'
            ], 403);
        }

        // Create token
        $token = $employee->createToken('e-kasir-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'username' => $employee->username,
                    'role' => $employee->role,
                ],
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
