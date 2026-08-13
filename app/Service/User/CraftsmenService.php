<?php

namespace App\Service\User;

use App\Models\Craftsmen;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class CraftsmenService
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

    $query = QueryBuilder::for(Craftsmen::class)
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
          $query->join('users', 'craftsmens.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('craftsmens.*');
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

  public function findOne(Craftsmen $craftsmen): Craftsmen
  {
    return $craftsmen->load(['user.funds.currencies', 'user.media']);
  }

  public function create(array $data, $imageFiles = null): Craftsmen
  {
    return DB::transaction(function () use ($data, $imageFiles) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      if ($imageFiles) {
        $this->attachMedia($user, $imageFiles);
      }

      $craftsmen = Craftsmen::create([
        'user_id' => $user->id,
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل حرفي جديد: {$user->name} (البريد: {$user->email})",
        affectedTable: 'craftsmen'
      );

      return $craftsmen->load('user.media');
    });
  }

  public function update(Craftsmen $craftsmen, array $data, $imageFiles = null, array $deletedMediaIds = []): Craftsmen
  {
    DB::transaction(function () use ($craftsmen, $data, $imageFiles, $deletedMediaIds) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $craftsmen->user()->update(collect($data)->except(['images', 'deleted_media_ids'])->toArray());

      if (!empty($deletedMediaIds)) {
        $mediaItems = $craftsmen->user->media()->whereIn('id', $deletedMediaIds)->get();
        foreach ($mediaItems as $media) {
          $media->delete();
        }
      }

      if ($imageFiles) {
        $this->attachMedia($craftsmen->user, $imageFiles);
      }

      $craftsmen->load('user.media');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الحرفي: {$craftsmen->user->name}",
        affectedTable: 'craftsmen'
      );
    });

    return $craftsmen->load('user.media');
  }

  public function delete(Craftsmen $craftsmen): bool
  {
    return DB::transaction(function () use ($craftsmen) {
      $craftsmenName = $craftsmen->user?->name ?? 'غير معروف';

      $deleted = (bool) $craftsmen->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الحرفي: {$craftsmenName}",
          affectedTable: 'craftsmen'
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
