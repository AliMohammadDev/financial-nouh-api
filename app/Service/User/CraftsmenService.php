<?php

namespace App\Service\User;

use App\Models\Craftsmen;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CraftsmenService
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
        });
      }),
    ];

    $query = QueryBuilder::for(Craftsmen::class)
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

  public function findOne(Craftsmen $craftsmen): Craftsmen
  {
    return $craftsmen->load('user');
  }

  public function create(array $data): Craftsmen
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $craftsmen = Craftsmen::create([
        'user_id' => $user->id,
      ]);

      return $craftsmen->load('user');
    });
  }

  public function update(Craftsmen $craftsmen, array $data): Craftsmen
  {
    DB::transaction(function () use ($craftsmen, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $craftsmen->user()->update($data);
    });

    return $craftsmen->load('user');
  }

  public function delete(Craftsmen $craftsmen): bool
  {
    return DB::transaction(function () use ($craftsmen) {
      return (bool) $craftsmen->user()->delete();
    });
  }
}
