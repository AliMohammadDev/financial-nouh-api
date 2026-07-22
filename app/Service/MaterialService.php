<?php

namespace App\Service;

use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MaterialService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->where('name', 'like', "%{$value}%")
          ->orWhere('description', 'like', "%{$value}%")
          ->orWhere('unit', 'like', "%{$value}%")
          ->orWhereHas('item', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
          });
      }),
    ];

    $query = QueryBuilder::for(Material::class)
      ->with('item')
      ->allowedFilters(...$filters)
      ->defaultSort('-created_at');

    if ($paginate) {
      return $query->paginate(
        perPage: $perPage,
        page: $page,
        columns: $columns,
      );
    }

    return $query->get($columns);
  }

  public function findOne(Material $material): Material
  {
    return $material->load('item');
  }

  public function create(array $data): Material
  {
    return DB::transaction(function () use ($data) {
      $material = Material::create($data);
      return $material->load('item');
    });
  }

  public function update(Material $material, array $data): Material
  {
    DB::transaction(function () use ($material, $data) {
      $material->update($data);
    });

    return $material->load('item');
  }

  public function delete(Material $material): bool
  {
    return DB::transaction(function () use ($material) {
      return (bool) $material->delete();
    });
  }
}
