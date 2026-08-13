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
        'user.media',
      ])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'suppliers.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('suppliers.*');
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
    return $supplier->load(['user.funds.currencies', 'user.media']);
  }

  public function create(array $data, $imageFiles = null): Supplier
  {
    return DB::transaction(function () use ($data, $imageFiles) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      if ($imageFiles) {
        $this->attachMedia($user, $imageFiles);
      }

      $supplier = Supplier::create([
        'user_id' => $user->id,
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل مورد جديد: {$user->name} (البريد: {$user->email})",
        affectedTable: 'suppliers'
      );

      return $supplier->load('user.media');
    });
  }

  public function update(Supplier $supplier, array $data, $imageFiles = null, array $deletedMediaIds = []): Supplier
  {
    DB::transaction(function () use ($supplier, $data, $imageFiles, $deletedMediaIds) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $supplier->user()->update(collect($data)->except(['images', 'deleted_media_ids'])->toArray());

      if (!empty($deletedMediaIds)) {
        $mediaItems = $supplier->user->media()->whereIn('id', $deletedMediaIds)->get();
        foreach ($mediaItems as $media) {
          $media->delete();
        }
      }

      if ($imageFiles) {
        $this->attachMedia($supplier->user, $imageFiles);
      }

      $supplier->load('user.media');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المورد: {$supplier->user->name}",
        affectedTable: 'suppliers'
      );
    });

    return $supplier->load('user.media');
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

  private function attachMedia(User $user, $imageFiles)
  {
    $files = is_array($imageFiles) ? $imageFiles : [$imageFiles];

    foreach ($files as $file) {
      if ($file) {
        $user->addMedia($file)->toMediaCollection('users_files');
      }
    }
  }
}
