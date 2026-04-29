<?php

namespace App\Modules\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = $this->authService->register($data);

        return response()->json($user, 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        try {
            $token = $this->authService->login($data);

            return response()->json(['token' => $token], 200);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'email_not_verified') {
                return response()->json(['error' => 'email_not_verified'], 403);
            }

            return response()->json(['error' => 'invalid_credentials'], 401);
        }
    }

    public function resendEmailVerification(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json(['message' => 'verification_email_sent'], 200);
    }
}
