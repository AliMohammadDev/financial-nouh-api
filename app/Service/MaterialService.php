<?php

namespace App\Service;

use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MaterialService
{
  public function findAll(): Collection
  {
    return Material::with('item')->get();
  }

  public function findOne(int $id): Material
  {
    return Material::with('item')->findOrFail($id);
  }

  public function create(array $data): Material
  {
    return DB::transaction(function () use ($data) {
      return Material::create($data);
    });
  }

  public function update(int $id, array $data): Material
  {
    $material = Material::findOrFail($id);

    DB::transaction(function () use ($material, $data) {
      $material->update($data);
    });

    return $material->load('item');
  }

  public function delete(int $id): bool
  {
    $material = Material::findOrFail($id);

    return DB::transaction(function () use ($material) {
      return (bool) $material->delete();
    });
  }
}
