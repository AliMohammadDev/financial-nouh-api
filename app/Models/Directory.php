<?php

namespace App\Models;

use App\MediaLibrary\DirectoryPathGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

#[Fillable([
  'dir_name',
  'dir_path',
  'parent_dir_id',
  'project_id',
  'user_id',
  'is_locked',
])]
class Directory extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  protected static function booting(): void
  {
    PathGeneratorFactory::setCustomPathGenerators(
      static::class,
      DirectoryPathGenerator::class
    );
  }

  protected static function booted()
  {
    static::creating(function ($directory) {
      if (empty($directory->dir_path)) {
        $slug = Str::slug($directory->dir_name);

        if ($directory->parent_dir_id) {
          $parent = self::find($directory->parent_dir_id);
          $directory->dir_path = $parent ? rtrim($parent->dir_path, '/') . '/' . $slug : '/' . $slug;
        } else {
          $directory->dir_path = '/' . $slug;
        }
      }
    });
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function parent(): BelongsTo
  {
    return $this->belongsTo(Directory::class, 'parent_dir_id');
  }

  public function children(): HasMany
  {
    return $this->hasMany(Directory::class, 'parent_dir_id');
  }
}
