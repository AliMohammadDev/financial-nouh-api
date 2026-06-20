<?php

namespace App\Service;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ItemService
{
  public function findAll(): Collection
  {
    return Item::with('materials')->get();
  }

  public function findOne(int $id): Item
  {
    return Item::with('materials')->findOrFail($id);
  }

  public function create(array $data): Item
  {
    return DB::transaction(function () use ($data) {
      return Item::create($data);
    });
  }

  public function update(int $id, array $data): Item
  {
    $item = Item::findOrFail($id);

    DB::transaction(function () use ($item, $data) {
      $item->update($data);
    });

    return $item->load('materials');
  }

  public function delete(int $id): bool
  {
    $item = Item::findOrFail($id);

    return DB::transaction(function () use ($item) {
      return (bool) $item->delete();
    });
  }
}
