<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectoryResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'            => $this->id,
      'dir_name'      => $this->dir_name,
      'dir_path'      => $this->dir_path,
      'parent_dir_id' => $this->parent_dir_id,
      'project'       => new ProjectResource($this->whenLoaded('project')),
      'children'      => DirectoryResource::collection($this->whenLoaded('children')),

      'parent'        => new DirectoryResource($this->whenLoaded('parent')),
      'files'         => $this->whenLoaded('media', function () {
        return $this->getMedia('directory_files')->map(function ($media) {
          return [
            'id'         => $media->id,
            'file_name'  => $media->file_name,
            'size'       => $media->human_readable_size,
            'extension'  => $media->extension,
            'url'        => $media->getUrl(),
            'created_at' => $media->created_at?->format('Y-m-d H:i:s'),
          ];
        });
      }),
      'created_at'    => $this->created_at?->format('Y-m-d'),
    ];
  }
}
