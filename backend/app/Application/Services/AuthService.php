<?php

namespace App\Application\Services;

use App\Domains\Auth\User\User;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthService
{
    /**
     * Authenticate user and return a token string.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new Exception("Invalid email or password.");
        }

        // Return a mock token string structured as valid-{userId}
        $token = 'valid-' . $user->id;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Register a new user.
     */
    public function register(array $data): array
    {  
        // Debugging line to check existing users

            $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'OPERATOR'
        ]);

        $token = 'valid-' . $user->id;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
