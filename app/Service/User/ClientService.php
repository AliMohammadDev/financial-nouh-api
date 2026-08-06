<?php

namespace App\Service\User;

use App\Models\Client;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ClientService
{
  public function __construct(
    private AuditLogService $auditLogService
  ) {}

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
      ->allowedSorts(
        'created_at',
        'id',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'clients.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('clients.*');
        }),
        AllowedSort::callback('user_email', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'clients.user_id', '=', 'users.id')
            ->orderBy('users.email', $direction)
            ->select('clients.*');
        })
      )
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
    return $client->load(['user.funds.currencies', 'projects']);
  }

  public function create(array $data): Client
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $client = Client::create([
        'user_id' => $user->id,
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل عميل جديد: {$user->name} (البريد: {$user->email})",
        affectedTable: 'clients'
      );

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
      $client->load('user');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات العميل: {$client->user->name}",
        affectedTable: 'clients'
      );
    });

    return $client->load(['user', 'projects']);
  }

  public function delete(Client $client): bool
  {
    return DB::transaction(function () use ($client) {
      $clientName = $client->user?->name ?? 'غير معروف';

      $deleted = (bool) $client->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف العميل: {$clientName}",
          affectedTable: 'clients'
        );
      }

      return $deleted;
    });
  }
}
