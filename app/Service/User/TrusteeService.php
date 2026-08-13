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
        'user.media',
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
    return $trustee->load(['user.funds.currencies', 'user.media']);
  }

  public function create(array $data, $imageFiles = null): Trustee
  {
    return DB::transaction(function () use ($data, $imageFiles) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      if ($imageFiles) {
        $this->attachMedia($user, $imageFiles);
      }

      $trustee = Trustee::create([
        'user_id' => $user->id,
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل وصي جديد: {$user->name}",
        affectedTable: 'trustees'
      );

      return $trustee->load('user.media');
    });
  }

  public function update(Trustee $trustee, array $data, $imageFiles = null, array $deletedMediaIds = []): Trustee
  {
    DB::transaction(function () use ($trustee, $data, $imageFiles, $deletedMediaIds) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      // تحديث بيانات جدول users مع استبعاد حقول الوسائط ومصفوفة الحذف
      $trustee->user->update(collect($data)->except(['images', 'deleted_media_ids'])->toArray());

      // 1. حذف الوسائط المحددة بناءً على الـ IDs المرسلة
      if (!empty($deletedMediaIds)) {
        $mediaItems = $trustee->user->media()->whereIn('id', $deletedMediaIds)->get();
        foreach ($mediaItems as $media) {
          $media->delete();
        }
      }

      // 2. إرفاق الصور الجديدة إن وجدت
      if ($imageFiles) {
        $this->attachMedia($trustee->user, $imageFiles);
      }

      $trustee->load('user.media');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الوصي: {$trustee->user->name}",
        affectedTable: 'trustees'
      );
    });

    return $trustee->load('user.media');
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
