<?php

namespace App\Service;

use App\Models\ProjectFund;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProjectFundService
{
  public function findAll(): Collection
  {
    return ProjectFund::with('project')->get();
  }

  public function findOne(int $id): ProjectFund
  {
    return ProjectFund::with('project')->findOrFail($id);
  }

  public function create(array $data): ProjectFund
  {
    return DB::transaction(function () use ($data) {
      return ProjectFund::create($data);
    });
  }

  public function update(int $id, array $data): ProjectFund
  {
    $fund = ProjectFund::findOrFail($id);

    DB::transaction(function () use ($fund, $data) {
      $fund->update($data);
    });

    return $fund;
  }

  public function delete(int $id): bool
  {
    $fund = ProjectFund::findOrFail($id);
    return DB::transaction(function () use ($fund) {
      return (bool) $fund->delete();
    });
  }
}
