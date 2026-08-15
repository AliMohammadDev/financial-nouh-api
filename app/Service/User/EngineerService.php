<?php

namespace App\Service\User;

use App\Models\Engineer;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class EngineerService
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
        })->orWhere('job_title', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Engineer::class)
      ->with([
        'user.funds.currencies',
        'user.media',
        'department'
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

  public function findOne(Engineer $engineer): Engineer
  {
    return $engineer->load(['user.funds.currencies', 'user.media', 'department']);
  }

  public function create(array $data, $imageFiles = null): Engineer
  {
    return DB::transaction(function () use ($data, $imageFiles) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      if ($imageFiles) {
        $this->attachMedia($user, $imageFiles);
      }

      $engineer = Engineer::create([
        'user_id'     => $user->id,
        'job_title'   => $data['job_title'],
        'base_salary' => $data['base_salary'],
        'department_id' => $data['department_id'],
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل مهندس جديد: {$user->name} (المسمى الوظيفي: {$engineer->job_title})",
        affectedTable: 'engineers'
      );

      return $engineer->load('user.media');
    });
  }

  public function update(Engineer $engineer, array $data, $imageFiles = null, array $deletedMediaIds = []): Engineer
  {
    DB::transaction(function () use ($engineer, $data, $imageFiles, $deletedMediaIds) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $engineer->user->update(
        collect($data)->except(['job_title', 'base_salary', 'department_id', 'status', 'images', 'deleted_media_ids'])->toArray()
      );

      if (isset($data['job_title']) || isset($data['base_salary']) || isset($data['department_id']) || isset($data['status'])) {
        $engineer->update(
          collect($data)->only(['job_title', 'base_salary', 'department_id', 'status'])->toArray()
        );
      }

      if ($imageFiles) {
        $this->attachMedia($engineer->user, $imageFiles);
      }

      $engineer->load('user.media');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المهندس: {$engineer->user->name}",
        affectedTable: 'engineers'
      );
    });

    return $engineer->load('user.media');
  }

  public function delete(Engineer $engineer): bool
  {
    return DB::transaction(function () use ($engineer) {
      $engineerName = $engineer->user?->name ?? 'غير معروف';

      $deleted = (bool) $engineer->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المهندس: {$engineerName}",
          affectedTable: 'engineers'
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
