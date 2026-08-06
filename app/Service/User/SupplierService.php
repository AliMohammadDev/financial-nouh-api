<?php

namespace App\Service\User;

use App\Models\Supplier;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class SupplierService
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

    $query = QueryBuilder::for(Supplier::class)
      ->with([
        'user.funds.currencies',
      ])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'job_title',
        'base_salary',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'engineers.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('engineers.*');
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

  public function findOne(Supplier $supplier): Supplier
  {
    return $supplier->load('user.funds.currencies');
  }

  public function create(array $data): Supplier
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $supplier = Supplier::create([
        'user_id' => $user->id,
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل مورد جديد: {$user->name} (البريد: {$user->email})",
        affectedTable: 'suppliers'
      );

      return $supplier->load('user');
    });
  }

  public function update(Supplier $supplier, array $data): Supplier
  {
    DB::transaction(function () use ($supplier, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }
      $supplier->user()->update($data);
      $supplier->load('user');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المورد: {$supplier->user->name}",
        affectedTable: 'suppliers'
      );
    });

    return $supplier->load('user');
  }

  public function delete(Supplier $supplier): bool
  {
    return DB::transaction(function () use ($supplier) {
      $supplierName = $supplier->user?->name ?? 'غير معروف';

      $deleted = (bool) $supplier->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المورد: {$supplierName}",
          affectedTable: 'suppliers'
        );
      }

      return $deleted;
    });
  }
}
