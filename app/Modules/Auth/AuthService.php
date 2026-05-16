<?php

namespace App\Modules\Auth;

use App\Constants\ErrorCode;
use App\Modules\User\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    public function forgotPassword(array $data): void
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw new BadRequestHttpException(ErrorCode::AUTH_INVALID_CREDENTIALS);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'reset_code' => $code,
            'reset_code_expires_at' => now()->addMinutes(15),
        ]);

        Notification::route('mail', $data['email'])->notify(new ResetPassword($user, $code));
    }

    public function resetPassword(array $data): void
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw new BadRequestHttpException(ErrorCode::AUTH_INVALID_CREDENTIALS);
        }

        if ($data['code'] !== $user->reset_code) {
            throw new BadRequestHttpException(ErrorCode::AUTH_INVALID_CREDENTIALS);
        }

        if (!$user->reset_code_expires_at || $user->reset_code_expires_at->isPast()) {
            throw new BadRequestHttpException(ErrorCode::AUTH_CODE_EXPIRED);
        }

        $user->update([
            'password' => bcrypt($data['password']),
            'reset_code' => null,
            'reset_code_expires_at' => null,
        ]);
    }
}
