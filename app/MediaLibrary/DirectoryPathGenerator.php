<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DirectoryPathGenerator implements PathGenerator
{
  public function getPath(Media $media): string
  {
    return 'directory_files/' . $media->id . '/';
  }

  public function getPathForConversions(Media $media): string
  {
    return 'directory_files/' . $media->id . '/conversions/';
  }

  public function getPathForResponsiveImages(Media $media): string
  {
    return 'directory_files/' . $media->id . '/responsive/';
  }
}