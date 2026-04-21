<?php

namespace App\Modules\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;


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

        $token = $this->authService->login($data);
        return response()->json(['token' => $token], 200);
    }

}
