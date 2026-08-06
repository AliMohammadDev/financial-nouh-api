<?php

namespace App\Service\User;

use App\Models\Trustee;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class TrusteeService
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

    $query = QueryBuilder::for(Trustee::class)
      ->with([
        'user.funds.currencies',
      ])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'trustees.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('trustees.*');
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
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل وصي جديد: {$user->name})",
        affectedTable: 'trustees'
      );

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

      $trustee->user->update(collect($data)->toArray());


      $trustee->load('user');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الوصي: {$trustee->user->name}",
        affectedTable: 'trustees'
      );
    });

    return $trustee->load('user');
  }

  public function delete(Trustee $trustee): bool
  {
    return DB::transaction(function () use ($trustee) {
      $trusteeName = $trustee->user?->name ?? 'غير معروف';

      $deleted = (bool) $trustee->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الوصي: {$trusteeName}",
          affectedTable: 'trustees'
        );
      }

      return $deleted;
    });
  }
}
