<?php

namespace App\Service\User;

use App\Models\Investor;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class InvestorService
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

    $query = QueryBuilder::for(Investor::class)
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
          $query->join('users', 'investors.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('investors.*');
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

  public function findOne(Investor $investor): Investor
  {
    return $investor->load(['user.funds.currencies', 'user.media']);
  }

  public function create(array $data, $imageFiles = null): Investor
  {
    return DB::transaction(function () use ($data, $imageFiles) {
      $data['password'] = Hash::make($data['password']);
      $user = User::create($data);

      if ($imageFiles) {
        $this->attachMedia($user, $imageFiles);
      }

      $investor = Investor::create([
        'user_id'          => $user->id,
        'investment_ratio' => $data['investment_ratio'] ?? null
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل مستثمر جديد: {$user->name} (نسبة الاستثمار: {$investor->investment_ratio}%)",
        affectedTable: 'investors'
      );

      return $investor->load('user.media');
    });
  }

  public function update(Investor $investor, array $data, $imageFiles = null, array $deletedMediaIds = []): Investor
  {
    DB::transaction(function () use ($investor, $data, $imageFiles, $deletedMediaIds) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $investor->user()->update(collect($data)->except(['investment_ratio', 'images', 'deleted_media_ids'])->toArray());

      if (isset($data['investment_ratio'])) {
        $investor->update(['investment_ratio' => $data['investment_ratio']]);
      }

      if (!empty($deletedMediaIds)) {
        $mediaItems = $investor->user->media()->whereIn('id', $deletedMediaIds)->get();
        foreach ($mediaItems as $media) {
          $media->delete();
        }
      }

      if ($imageFiles) {
        $this->attachMedia($investor->user, $imageFiles);
      }

      $investor->load('user.media');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المستثمر: {$investor->user->name}",
        affectedTable: 'investors'
      );
    });

    return $investor->load('user.media');
  }

  public function delete(Investor $investor): bool
  {
    return DB::transaction(function () use ($investor) {
      $investorName = $investor->user?->name ?? 'غير معروف';

      $deleted = (bool) $investor->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المستثمر: {$investorName}",
          affectedTable: 'investors'
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
