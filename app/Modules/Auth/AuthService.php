<?php

namespace App\Modules\Auth;

use App\Modules\User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct() {}

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

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new \Exception('invalid_credentials');
        }

        if (! $user->hasVerifiedEmail()) {
            throw new \Exception('email_not_verified');
        }

        return $user->createToken('api-token')->plainTextToken;
    }
}
