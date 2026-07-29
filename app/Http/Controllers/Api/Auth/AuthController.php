<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Service\auth\AuthService;
use App\Models\User;

class AuthController extends Controller
{
  public function __construct(
    private AuthService $authService
  ) {}

  public function login(LoginRequest $request)
  {
    $token = $this->authService->loginUser($request->validated());

    if (!$token) {
      return response()->json([
        'message' => 'Invalid email or password',
      ], 401);
    }
    $user = User::where('email', $request->email)->first();
    // $user->loadMissing(['roles']);

    return response()->json([
      'token'   => $token,
      'user'    => new UserResource($user)
    ]);
  }

  public function me()
  {
    $user = Auth::user();

    // $user->loadMissing(['roles']);
    return new UserResource($user);
  }

  public function logout()
  {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    if ($user) {
      $user->currentAccessToken()->delete();
    }

    return response()->json([
      'message' => 'Logged out successfully',
    ], 200);
  }
}
