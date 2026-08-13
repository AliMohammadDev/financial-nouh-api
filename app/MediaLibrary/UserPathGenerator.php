<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserPathGenerator implements PathGenerator
{
  public function getPath(Media $media): string
  {
    return 'users_files/' . $media->id . '/';
  }

  public function getPathForConversions(Media $media): string
  {
    return 'users_files/' . $media->id . '/conversions/';
  }

  public function getPathForResponsiveImages(Media $media): string
  {
    return 'users_files/' . $media->id . '/responsive/';
  }
}
