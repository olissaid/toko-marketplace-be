<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Fitur 4.1 - Login User
     */
    public function login(Request $request)
    {
        // 1. Validasi input request body sesuai kontrak
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR'
            ], 422);
        }

        // 2. Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // 3. Cek apakah user ada dan password-nya benar (Bcrypt)
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
                'code' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        // 4. Jika sukses, buat token baru lewat Laravel Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Kembalikan response sukses sesuai format API Contract
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role, // admin | staff
                    'created_at' => $user->created_at->toISOString(),
                ]
            ]
        ], 200);
    }

    /**
     * Fitur 4.3 - Get Current User (Cek Session)
     */
    public function me(Request $request)
    {
        // Mengambil data user yang sedang login saat ini berdasarkan tokennya
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 200);
    }

    /**
     * Fitur 4.2 - Logout User
     */
    public function logout(Request $request)
    {
        // Hapus token yang saat ini sedang digunakan untuk login
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }
}