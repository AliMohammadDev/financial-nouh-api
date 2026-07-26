<?php

namespace App\Service;

use App\Models\Revenue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RevenueService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $query = Revenue::with(['user', 'receiver', 'revenueable'])
      ->latest();

    if ($paginate) {
      return $query->paginate(perPage: $perPage, page: $page, columns: $columns);
    }

    return $query->get($columns);
  }

  public function findOne(Revenue $revenue): Revenue
  {
    return $revenue->load(['user', 'receiver', 'revenueable']);
  }

  public function create(array $data): Revenue
  {
    return DB::transaction(function () use ($data) {
      $revenue = Revenue::create($data); // الـ Observer سيقوم بزيادة الرصيد تلقائياً
      return $revenue->load(['user', 'receiver', 'revenueable']);
    });
  }

  public function update(Revenue $revenue, array $data): Revenue
  {
    return DB::transaction(function () use ($revenue, $data) {
      $revenue->update($data); // الـ Observer سيقوم بتعديل الأرصدة تلقائياً
      return $revenue->load(['user', 'receiver', 'revenueable']);
    });
  }

  public function delete(Revenue $revenue): bool
  {
    return DB::transaction(function () use ($revenue) {
      return (bool) $revenue->delete(); // الـ Observer سيقوم بخصم المبلغ عند الحذف
    });
  }
}
