<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
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
        'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
      ], 401);
    }
    $user = User::where('email', $request->email)->first();
    // $user->loadMissing(['roles']);

    $user->load([
      'client',
      'employee',
      'admin',
      'engineer',
      'craftsmen',
      'supplier',
      'trustee',
      'investor',
      'dailyWorker',
    ]);

    return response()->json([
      'token'   => $token,
      'user'    => new UserResource($user)
    ]);
  }

  public function me()
  {

    $user = Auth::user();

    // $user->loadMissing(['roles']);
    $user->load([
      'client',
      'employee',
      'admin',
      'engineer',
      'craftsmen',
      'supplier',
      'trustee',
      'investor',
      'dailyWorker',
    ]);
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
      'message' => 'تم تسجيل الخروج بنجاح',
    ], 200);
  }

  public function updatePassword(UpdatePasswordRequest $request)
  {
    $updated = $this->authService->updatePasswordByEmail($request->validated());

    if (!$updated) {
      return response()->json([
        'message' => 'حدث خطأ ما، البريد الإلكتروني غير موجود',
      ], 404);
    }

    return response()->json([
      'message' => 'تم تغيير كلمة المرور بنجاح',
    ], 200);
  }
}
