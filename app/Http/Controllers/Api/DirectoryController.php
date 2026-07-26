<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\CreateDirectoryRequest;
use App\Http\Requests\Directory\MoveFileRequest;
use App\Http\Requests\Directory\UpdateDirectoryRequest;
use App\Http\Requests\Directory\UploadFileRequest;
use App\Http\Resources\DirectoryResource;
use App\Models\Directory;
use App\Service\DirectoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DirectoryController extends Controller
{
  public function __construct(
    private DirectoryService $directoryService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $directories = $this->directoryService->findAll($paginate, $perPage, $page);

    return DirectoryResource::collection($directories);
  }

  public function store(CreateDirectoryRequest $request): JsonResponse
  {
    $directory = $this->directoryService->create($request->validated());
    return response()->json(new DirectoryResource($directory), Response::HTTP_CREATED);
  }

  public function show(Directory $directory): JsonResponse
  {
    $directoryWithRelations = $this->directoryService->findOne($directory);
    return response()->json(new DirectoryResource($directoryWithRelations));
  }

  public function update(UpdateDirectoryRequest $request, Directory $directory): JsonResponse
  {
    $updatedDirectory = $this->directoryService->update($directory, $request->validated());
    return response()->json(new DirectoryResource($updatedDirectory));
  }

  public function destroy(Directory $directory): JsonResponse
  {
    $this->directoryService->delete($directory);
    return response()->json([
      'message' => 'Directory deleted successfully'
    ], Response::HTTP_OK);
  }

  public function uploadFile(UploadFileRequest $request, Directory $directory): JsonResponse
  {
    $updatedDirectory = $this->directoryService->uploadFile($directory, $request->file('files'));

    return response()->json([
      'message' => 'Files uploaded to directory successfully',
      'data'    =>    new DirectoryResource($updatedDirectory)
    ], Response::HTTP_OK);
  }
  public function deleteFile(Directory $directory, int $mediaId): JsonResponse
  {
    $this->directoryService->deleteFile($directory, $mediaId);

    return response()->json([
      'message' => 'File deleted from directory successfully'
    ], Response::HTTP_OK);
  }

  public function moveFile(MoveFileRequest $request): JsonResponse
  {
    $this->directoryService->moveFile(
      $request->input('media_id'),
      $request->input('target_directory_id')
    );

    return response()->json([
      'message' => 'File moved to another directory successfully'
    ], Response::HTTP_OK);
  }
}
