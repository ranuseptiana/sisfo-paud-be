<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json(['error' => 'Username not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Password mismatch'], 401);
        }

        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     throw ValidationException::withMessages([
        //         'username' => ['The provided credentials are incorrect.'],
        //     ]);
        // }

        $token = $user->createToken('login-token')->plainTextToken;
        $expires_at = now()->addMinutes(2);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'expires_at' => $expires_at,
            'user_type' => $user->user_type
        ]);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $newToken = $user->createToken('login-token')->plainTextToken;

        return response()->json([
            'token' => $newToken,
            'expires_at' => now()->addMinutes(60)
        ]);
    }

    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'logout success']);
    }
}
