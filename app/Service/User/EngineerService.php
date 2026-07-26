<?php

namespace App\Service\User;

use App\Models\Engineer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EngineerService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->whereHas('user', function ($q) use ($value) {
          $q->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone_number', 'like', "%{$value}%");
        })->orWhere('job_title', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Engineer::class)
      ->with([
        'user.funds.currencies',
      ])
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

  public function findOne(Engineer $engineer): Engineer
  {
    return $engineer->load('user.funds.currencies');
  }

  public function create(array $data): Engineer
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $engineer = Engineer::create([
        'user_id'     => $user->id,
        'job_title'   => $data['job_title'],
        'base_salary' => $data['base_salary'],
      ]);

      return $engineer->load('user');
    });
  }

  public function update(Engineer $engineer, array $data): Engineer
  {
    DB::transaction(function () use ($engineer, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $engineer->user->update(collect($data)->except(['job_title', 'base_salary'])->toArray());

      $engineer->update(collect($data)->only(['job_title', 'base_salary'])->toArray());
    });

    return $engineer->load('user');
  }

  public function delete(Engineer $engineer): bool
  {
    return DB::transaction(function () use ($engineer) {
      return (bool) $engineer->user()->delete();
    });
  }
}
