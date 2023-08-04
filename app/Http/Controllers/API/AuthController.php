<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $user = User::where('email', '=', $request->input('email'))->firstOrFail();

            if (!Hash::check($request->input('password'), $user->password)) {
                return response()->json([
                    'Error' => 'Auth failed',
                    'Message' => 'Invalid credentials'
                ], 401);
            }

            $token = $user->createToken('user_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => $e->getMessage(),
                'Message' => 'Something went wrong'
            ], 400);
        }
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password'))
            ]);

            $token = $user->createToken('user_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => $e->getMessage(),
                'Message' => 'Something went wrong'
            ], 400);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = User::findOrFail($request->input('user_id'));
            $user->tokens()->delete();

            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => $e->getMessage(),
                'Message' => 'Something went wrong'
            ], 400);
        }
    }
}
