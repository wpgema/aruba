<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Named route for login - required by auth middleware
Route::get('/login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized. Please login first.',
        'login_url' => '/api/auth/login'
    ], 401);
})->name('login');
