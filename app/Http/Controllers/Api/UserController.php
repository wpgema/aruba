<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('sales');

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection($users->items()),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show(User $user)
    {
        $user->loadCount('sales');
        
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = $request->only(['name', 'email']);
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function destroy(User $user)
    {
        // Check if user has sales
        if ($user->sales()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete user with existing sales'
            ], 422);
        }
        
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->loadCount('sales');
        
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password',
            'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
        ]);

        // Verify current password if trying to change password
        if ($request->has('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
        }

        $data = $request->only(['name', 'email']);
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user)
        ]);
    }
}