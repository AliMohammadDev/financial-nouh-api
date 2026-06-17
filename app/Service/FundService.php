<?php

namespace App\Service;

use App\Models\Fund;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FundService
{
  public function findAll(): Collection
  {
    return Fund::with('user')->get();
  }

  public function findOne(int $id): Fund
  {
    return Fund::with('user')->findOrFail($id);
  }

  public function create(array $data): Fund
  {
    return DB::transaction(function () use ($data) {
      return Fund::create($data);
    });
  }

  public function update(int $id, array $data): Fund
  {
    $fund = Fund::findOrFail($id);

    DB::transaction(function () use ($fund, $data) {
      $fund->update($data);
    });

    return $fund->load('user');
  }

  public function delete(int $id): bool
  {
    $fund = Fund::findOrFail($id);

    return DB::transaction(function () use ($fund) {
      return (bool) $fund->delete();
    });
  }
}
