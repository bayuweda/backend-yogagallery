<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Handle the incoming registration request.
     */
    public function __invoke(Request $request)
    {
        // Validasi data input
       

        // Buat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate token menggunakan Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response
        return response()->json([
            'user' => $user,
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}
