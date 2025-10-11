<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $remember = $request->input('remember', false);
        $employee = Employee::where('username', $credentials['username'])->first();

        if (!$employee || !Hash::check($credentials['password'], $employee->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        if (!$employee->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan'
            ], 403);
        }

        $tokenResult = $employee->createToken('e-kasir-token');
        $token = $tokenResult->plainTextToken;

        try {
            if (!$remember) {
                $expiry = now()->addDay();
                $tokenResult->accessToken->expires_at = $expiry;
                $tokenResult->accessToken->save();
            }
        } catch (\Throwable $e) {
        }

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
        try {
            // First try to delete the currently authenticated access token
            $current = $request->user()->currentAccessToken();
            if ($current) {
                $current->delete();
                return response()->json(['message' => 'Logged out']);
            }

            // Fallback: try to delete by bearer token string
            $bearer = $request->bearerToken();
            if ($bearer) {
                $tokenModel = PersonalAccessToken::findToken($bearer);
                if ($tokenModel) {
                    $tokenModel->delete();
                    return response()->json(['message' => 'Logged out']);
                }
            }

            return response()->json(['message' => 'No token found to delete'], 400);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Logout failed', 'error' => $e->getMessage()], 500);
        }
    }
}
