<?php

namespace App\Http\Trait\User;

trait HasUserValidation
{

  protected function userCreateRules(): array
  {
    return [
      'name'         => ['required', 'string', 'max:255'],
      'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
      'password'     => ['required', 'string', 'min:8'],
      'phone_number' => ['nullable', 'string', 'max:20'],
      'address'      => ['nullable', 'string', 'max:255'],
    ];
  }


  protected function userUpdateRules(?int $userId): array
  {
    return [
      'name'         => ['sometimes', 'required', 'string', 'max:255'],
      'email'        => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
      'password'     => ['sometimes', 'nullable', 'string', 'min:8'],
      'phone_number' => ['nullable', 'string', 'max:20'],
      'address'      => ['nullable', 'string', 'max:255'],
    ];
  }
}
