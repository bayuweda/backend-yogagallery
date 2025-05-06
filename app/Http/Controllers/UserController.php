<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function profile(Request $request)
    {
        $user = $request->user(); // user yang sedang login

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $user->photo
                ? asset('storage/' . $user->photo)
                : asset('images/profile.jpg'), // default
        ]);
    }
}
