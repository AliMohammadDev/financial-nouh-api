<?php

namespace App\Service\User;

use App\Models\Investor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InvestorService
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

    $query = QueryBuilder::for(Investor::class)
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

  public function findOne(Investor $investor): Investor
  {
    return $investor->load('user');
  }

  public function create(array $data): Investor
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);
      $user = User::create($data);

      return Investor::create([
        'user_id' => $user->id,
        'investment_ratio' => $data['investment_ratio']
      ])->load('user');
    });
  }

  public function update(Investor $investor, array $data): Investor
  {
    DB::transaction(function () use ($investor, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $investor->user()->update(array_diff_key($data, ['investment_ratio' => 1]));

      if (isset($data['investment_ratio'])) {
        $investor->update(['investment_ratio' => $data['investment_ratio']]);
      }
    });

    return $investor->load('user');
  }

  public function delete(Investor $investor): bool
  {
    return DB::transaction(function () use ($investor) {
      return (bool) $investor->user()->delete();
    });
  }
}
