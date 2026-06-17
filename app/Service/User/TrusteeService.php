<?php

namespace App\Service\User;

use App\Models\Trustee;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TrusteeService
{
  public function findAll(): Collection
  {
    return Trustee::with('user')->get();
  }

  public function findOne(int $id): Trustee
  {
    return Trustee::with('user')->findOrFail($id);
  }

  public function create(array $data): Trustee
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $trustee = Trustee::create([
        'user_id'          => $user->id,
        'kinship_relation' => $data['kinship_relation'],
      ]);

      return $trustee->load('user');
    });
  }

  public function update(int $id, array $data): Trustee
  {
    $trustee = Trustee::with('user')->findOrFail($id);

    DB::transaction(function () use ($trustee, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $trustee->user->update(collect($data)->except(['kinship_relation'])->toArray());

      $trustee->update(collect($data)->only(['kinship_relation'])->toArray());
    });

    return $trustee->load('user');
  }

  public function delete(int $id): bool
  {
    $trustee = Trustee::findOrFail($id);

    return DB::transaction(function () use ($trustee) {
      return (bool) $trustee->user()->delete();
    });
  }
}
