<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Engineer\CreateEngineerRequest;
use App\Http\Requests\User\Engineer\UpdateEngineerRequest;
use App\Http\Resources\User\EngineerResource;
use App\Service\User\EngineerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EngineerController extends Controller
{
  public function __construct(
    private EngineerService $engineerService
  ) {}

  public function index(): JsonResponse
  {
    $engineers = $this->engineerService->findAll();
    return response()->json(EngineerResource::collection($engineers));
  }

  public function store(CreateEngineerRequest $request): JsonResponse
  {
    $engineer = $this->engineerService->create($request->validated());
    return response()->json(new EngineerResource($engineer), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $engineer = $this->engineerService->findOne($id);
    return response()->json(new EngineerResource($engineer));
  }

  public function update(UpdateEngineerRequest $request, int $id): JsonResponse
  {
    $engineer = $this->engineerService->update($id, $request->validated());
    return response()->json(new EngineerResource($engineer));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->engineerService->delete($id);
    return response()->json([
      'message' => 'Engineer deleted successfully'
    ], Response::HTTP_OK);
  }
}
