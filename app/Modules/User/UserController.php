<?php

namespace App\Modules\User;

use App\Http\Controllers\Controller;
use App\Modules\User\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
    }

    public function getMe(Request $request): JsonResponse
    {
        return $this->success($this->userService->getMe($request->user()));
    }

    public function updateMe(UpdateUserRequest $request): JsonResponse
    {
        return $this->success($this->userService->updateMe($request->user(), $request->validated()));
    }
}
