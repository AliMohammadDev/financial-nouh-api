<?php

namespace App\Service\User;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ClientService
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

    $query = QueryBuilder::for(Client::class)
      ->with([
        'user.funds.currencies',
        'projects'
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

  public function findOne(Client $client): Client
  {
    return $client->load(['user', 'projects']);
  }

  public function create(array $data): Client
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $client = Client::create([
        'user_id' => $user->id,
      ]);

      return $client->load(['user', 'projects']);
    });
  }

  public function update(Client $client, array $data): Client
  {
    DB::transaction(function () use ($client, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $client->user()->update($data);
    });

    return $client->load(['user', 'projects']);
  }

  public function delete(Client $client): bool
  {
    return DB::transaction(function () use ($client) {
      return (bool) $client->user()->delete();
    });
  }
}
