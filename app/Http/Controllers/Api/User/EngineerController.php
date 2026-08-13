<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Engineer\CreateEngineerRequest;
use App\Http\Requests\User\Engineer\UpdateEngineerRequest;
use App\Http\Resources\User\EngineerResource;
use App\Models\Engineer;
use App\Service\User\EngineerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EngineerController extends Controller
{
  public function __construct(
    private EngineerService $engineerService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $engineers = $this->engineerService->findAll($paginate, $perPage, $page);

    return EngineerResource::collection($engineers);
  }

  public function store(CreateEngineerRequest $request): JsonResponse
  {
    $engineer = $this->engineerService->create(
      $request->validated(),
      $request->file('images')
    );
    return response()->json(new EngineerResource($engineer), Response::HTTP_CREATED);
  }

  public function show(Engineer $engineer): JsonResponse
  {
    $engineerWithRelations = $this->engineerService->findOne($engineer);
    return response()->json(new EngineerResource($engineerWithRelations));
  }

  public function update(UpdateEngineerRequest $request, Engineer $engineer): JsonResponse
  {
    $deletedMediaIds = $request->input('deleted_media_ids', []);

    $updatedEngineer = $this->engineerService->update(
      $engineer,
      $request->validated(),
      $request->file('images'),
      $deletedMediaIds
    );

    return response()->json(new EngineerResource($updatedEngineer));
  }

  public function destroy(Engineer $engineer): JsonResponse
  {
    $this->engineerService->delete($engineer);
    return response()->json([
      'message' => 'Engineer deleted successfully'
    ], Response::HTTP_OK);
  }
}
