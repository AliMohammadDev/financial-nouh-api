<?php

namespace App\Service\User;

use App\Models\Craftsmen;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CraftsmenService
{
  public function findAll(): Collection
  {
    return Craftsmen::with('user')->get();
  }

  public function findOne(int $id): Craftsmen
  {
    return Craftsmen::with('user')->findOrFail($id);
  }

  public function create(array $data): Craftsmen
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $Craftsmen = Craftsmen::create([
        'user_id' => $user->id,
      ]);

      return $Craftsmen->load('user');
    });
  }

  public function update(int $id, array $data): Craftsmen
  {
    $Craftsmen = Craftsmen::findOrFail($id);

    DB::transaction(function () use ($Craftsmen, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $Craftsmen->user()->update($data);
    });

    return $Craftsmen->load('user');
  }

  public function delete(int $id): bool
  {
    $Craftsmen = Craftsmen::findOrFail($id);

    return DB::transaction(function () use ($Craftsmen) {
      return (bool) $Craftsmen->user()->delete();
    });
  }
}
