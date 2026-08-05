<?php

namespace App\Service;

use App\Models\Directory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DirectoryService
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
        $query->where('dir_name', 'like', "%{$value}%");
      }),
      AllowedFilter::exact('project_id'),
    ];

    $query = QueryBuilder::for(Directory::class)
      ->with(['project', 'children.media', 'media'])
      ->allowedFilters(...$filters);

    $query->whereNull('parent_dir_id');

    $query->defaultSort('-created_at');

    if ($paginate) {
      return $query->paginate(perPage: $perPage, page: $page, columns: $columns);
    }

    return $query->get($columns);
  }

  public function findOne(Directory $directory): Directory
  {
    return $directory->load([
      'project',
      'parent.parent.parent',
      'children.media',
      'media'
    ]);
  }
  public function create(array $data): Directory
  {
    return DB::transaction(function () use ($data) {
      $directory = Directory::create($data);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء مجلد جديد: {$directory->dir_name}",
        affectedTable: 'directories'
      );

      return $directory;
    });
  }

  public function update(Directory $directory, array $data): Directory
  {
    return DB::transaction(function () use ($directory, $data) {
      $directory->update($data);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المجلد: {$directory->dir_name}",
        affectedTable: 'directories'
      );

      return $directory;
    });
  }

  public function delete(Directory $directory): bool
  {
    return DB::transaction(function () use ($directory) {
      $dirName = $directory->dir_name ?? 'غير معروف';

      $directory->clearMediaCollection('directory_files');

      foreach ($directory->children as $child) {
        $this->delete($child);
      }

      $deleted = (bool) $directory->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المجلد: {$dirName}",
          affectedTable: 'directories'
        );
      }

      return $deleted;
    });
  }

  public function uploadFile(Directory $directory, array $files): Directory
  {
    return DB::transaction(function () use ($directory, $files) {
      foreach ($files as $file) {
        $directory->addMedia($file)
          ->toMediaCollection('directory_files', 'public');
      }
      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام برفع ملفات جديدة إلى المجلد: {$directory->dir_name}",
        affectedTable: 'directories'
      );
      return $directory->load(['media', 'project', 'children']);
    });
  }

  public function deleteFile(Directory $directory, int $mediaId): bool
  {
    return DB::transaction(function () use ($directory, $mediaId) {
      $media = $directory->media()->findOrFail($mediaId);
      $mediaName = $media->file_name ?? 'ملف';
      $media->delete();

      $this->auditLogService->log(
        actionType: 'حذف',
        description: "قام بحذف الملف ({$mediaName}) من المجلد: {$directory->dir_name}",
        affectedTable: 'directories'
      );


      return true;
    });
  }

  public function moveFile(int $mediaId, int $targetDirectoryId): bool
  {
    return DB::transaction(function () use ($mediaId, $targetDirectoryId) {
      $mediaItem = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);

      if ($mediaItem->model_type !== Directory::class) {
        throw new \Exception('The specified media is not a directory file.');
      }

      $mediaItem->model_id = $targetDirectoryId;
      $mediaItem->save();

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بنقل الملف (ID: {$mediaId}) إلى المجلد رقم: {$targetDirectoryId}",
        affectedTable: 'directories'
      );

      return true;
    });
  }

  public function copyFile(int $mediaId, int $targetDirectoryId): bool
  {
    return DB::transaction(function () use ($mediaId, $targetDirectoryId) {
      $mediaItem = Media::findOrFail($mediaId);

      if ($mediaItem->model_type !== Directory::class) {
        throw new \Exception('The specified media is not a directory file.');
      }

      $targetDirectory = Directory::findOrFail($targetDirectoryId);

      $mediaItem->copy($targetDirectory, 'directory_files', 'public');

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بنسخ الملف ({$mediaItem->file_name}) إلى المجلد رقم: {$targetDirectoryId}",
        affectedTable: 'directories'
      );

      return true;
    });
  }
}
