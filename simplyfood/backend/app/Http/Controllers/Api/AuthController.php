<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || $user->status !== 'ACTIVE' || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais invalidas',
            ], 401);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ],
            'message' => 'Login realizado com sucesso',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['status' => 'success', 'data' => null, 'message' => 'OK']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
            'message' => 'OK',
        ]);
    }
}
