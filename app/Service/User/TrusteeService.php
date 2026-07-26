<?php

namespace App\Service\User;

use App\Models\Trustee;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TrusteeService
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
        })->orWhere('kinship_relation', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Trustee::class)
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

  public function findOne(Trustee $trustee): Trustee
  {
    return $trustee->load('user.funds.currencies');
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

  public function update(Trustee $trustee, array $data): Trustee
  {
    DB::transaction(function () use ($trustee, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $trustee->user->update(collect($data)->except(['kinship_relation'])->toArray());

      if (isset($data['kinship_relation'])) {
        $trustee->update(collect($data)->only(['kinship_relation'])->toArray());
      }
    });

    return $trustee->load('user');
  }

  public function delete(Trustee $trustee): bool
  {
    return DB::transaction(function () use ($trustee) {
      return (bool) $trustee->user()->delete();
    });
  }
}
