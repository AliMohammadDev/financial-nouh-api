<?php

namespace App\Service\User;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
  public function findAll(): Collection
  {
    return Admin::with('user')->get();
  }

  public function findOne(int $id): Admin
  {
    return Admin::with('user')->findOrFail($id);
  }

  public function create(array $data): Admin
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);
      $user = User::create($data);

      return Admin::create(['user_id' => $user->id])->load('user');
    });
  }

  public function update(int $id, array $data): Admin
  {
    $admin = Admin::findOrFail($id);

    DB::transaction(function () use ($admin, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }
      $admin->user()->update($data);
    });

    return $admin->load('user');
  }

  public function delete(int $id): bool
  {
    $admin = Admin::findOrFail($id);
    return DB::transaction(function () use ($admin) {
      return (bool) $admin->user()->delete();
    });
  }
}
