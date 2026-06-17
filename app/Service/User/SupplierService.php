<?php

namespace App\Service\User;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SupplierService
{
  public function findAll(): Collection
  {
    return Supplier::with('user')->get();
  }

  public function findOne(int $id): Supplier
  {
    return Supplier::with('user')->findOrFail($id);
  }

  public function create(array $data): Supplier
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $supplier = Supplier::create([
        'user_id' => $user->id,
      ]);

      return $supplier->load('user');
    });
  }

  public function update(int $id, array $data): Supplier
  {
    $supplier = Supplier::findOrFail($id);

    DB::transaction(function () use ($supplier, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $supplier->user()->update($data);
    });

    return $supplier->load('user');
  }

  public function delete(int $id): bool
  {
    $supplier = Supplier::findOrFail($id);

    return DB::transaction(function () use ($supplier) {
      return (bool) $supplier->user()->delete();
    });
  }
}
