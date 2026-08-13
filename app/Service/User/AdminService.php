<?php

namespace App\Service\User;

use App\Models\Admin;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class AdminService
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

    $query = QueryBuilder::for(Admin::class)
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
          $query->join('users', 'admins.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('admins.*');
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

  public function findOne(Admin $admin): Admin
  {
    return $admin->load(['user.funds.currencies', 'user.media']);
  }

  public function create(array $data, $imageFiles = null): Admin
  {
    return DB::transaction(function () use ($data, $imageFiles) {
      $data['password'] = Hash::make($data['password']);
      $user = User::create($data);

      if ($imageFiles) {
        $this->attachMedia($user, $imageFiles);
      }

      $admin = Admin::create(['user_id' => $user->id])->load('user.media');

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل مشرف جديد: {$user->name} (البريد: {$user->email})",
        affectedTable: 'admins'
      );

      return $admin;
    });
  }

  public function update(Admin $admin, array $data, $imageFiles = null, array $deletedMediaIds = []): Admin
  {
    DB::transaction(function () use ($admin, $data, $imageFiles, $deletedMediaIds) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $admin->user()->update(collect($data)->except(['images', 'deleted_media_ids'])->toArray());

      if (!empty($deletedMediaIds)) {
        $mediaItems = $admin->user->media()->whereIn('id', $deletedMediaIds)->get();
        foreach ($mediaItems as $media) {
          $media->delete();
        }
      }

      if ($imageFiles) {
        $this->attachMedia($admin->user, $imageFiles);
      }

      $admin->load('user.media');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المشرف: {$admin->user->name}",
        affectedTable: 'admins'
      );
    });

    return $admin;
  }

  public function delete(Admin $admin): bool
  {
    return DB::transaction(function () use ($admin) {
      $adminName = $admin->user?->name ?? 'غير معروف';

      $deleted = (bool) $admin->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المشرف: {$adminName}",
          affectedTable: 'admins'
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
