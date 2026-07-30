<?php

namespace App\Service\auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{

  public function loginUser(array $data)
  {
    $user = User::where('email', $data['email'])->first();
    if (!$user || !Hash::check($data['password'], $user->password)) {
      return null;
    }

    if (!$user->is_active) {
      abort(403, 'User is not active');
    }

    // $user->tokens()->delete(); 
    $token = $user->createToken('auth_token')->plainTextToken;
    return $token;
  }

  public function updateProfile(User $user, array $data): User
  {
    if (!empty($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    } else {
      unset($data['password']);
    }

    $user->update($data);

    return $user;
  }
}