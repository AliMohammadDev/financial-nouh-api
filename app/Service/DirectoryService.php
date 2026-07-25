<?php

namespace App\Service;

use App\Models\Directory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DirectoryService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {
    $query = Directory::with(['project', 'children', 'media'])->latest();

    if ($paginate) {
      return $query->paginate(perPage: $perPage, page: $page, columns: $columns);
    }

    return $query->get($columns);
  }

  public function findOne(Directory $directory): Directory
  {
    return $directory->load(['project', 'children', 'media']);
  }

  public function create(array $data): Directory
  {
    return DB::transaction(function () use ($data) {
      return Directory::create($data);
    });
  }

  public function update(Directory $directory, array $data): Directory
  {
    return DB::transaction(function () use ($directory, $data) {
      $directory->update($data);
      return $directory;
    });
  }

  public function delete(Directory $directory): bool
  {
    return DB::transaction(function () use ($directory) {
      $directory->clearMediaCollection('directory_files');

      foreach ($directory->children as $child) {
        $this->delete($child);
      }

      return (bool) $directory->delete();
    });
  }

  public function uploadFile(Directory $directory, array $files): Directory
  {
    return DB::transaction(function () use ($directory, $files) {
      foreach ($files as $file) {
        $directory->addMedia($file)
          ->toMediaCollection('directory_files', 'public');
      }

      return $directory->load(['media', 'project', 'children']);
    });
  }

  public function deleteFile(Directory $directory, int $mediaId): bool
  {
    return DB::transaction(function () use ($directory, $mediaId) {
      $media = $directory->media()->findOrFail($mediaId);
      $media->delete();
      return true;
    });
  }
}
