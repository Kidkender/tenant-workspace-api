<?php

namespace App\Modules\Auth;

use App\Constants\ErrorCode;
use App\Modules\User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct()
    {
    }

    public function register(array $data): User
    {
        $user = User::create([
            'id' => Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        event(new Registered($user));

        return $user;
    }

    public function login(array $data): string
    {
        $user = User::where('email', $data['email'])->first();


        if (!$user || !Hash::check($data['password'], $user->password)) {
            Log::info('vao day');
            throw new \Exception(ErrorCode::AUTH_INVALID_CREDENTIALS);
        }

        if (!$user->hasVerifiedEmail()) {
            throw new \Exception(ErrorCode::AUTH_EMAIL_NOT_VERIFIED);
        }

        return $user->createToken('api-token')->plainTextToken;
    }
}
