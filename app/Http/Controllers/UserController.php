<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
                : asset('images/profile.jpg'),
        ]);
    }

    /**
     * Update user data
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'email'     => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'      => 'sometimes|required|in:owner,admin',
            'address'   => 'nullable|string|max:255',
            'birthdate' => 'nullable|date_format:Y-m-d',
            'phone'     => 'nullable|string|max:20',
            'password'  => 'nullable|string|min:6',
        ]);

        if ($request->has('name')) $user->name = $validated['name'];
        if ($request->has('email')) $user->email = $validated['email'];
        if ($request->has('role')) $user->role = $validated['role'];
        if ($request->has('address')) $user->address = $validated['address'];
        if ($request->has('birthdate')) $user->birthdate = $validated['birthdate'];
        if ($request->has('phone')) $user->phone = $validated['phone'];
        if ($request->has('password') && $validated['password']) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'user' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        try {
            $user->delete();
            return response()->json([
                'message' => 'User berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
