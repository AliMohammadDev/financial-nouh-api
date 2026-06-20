<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Material\CreateMaterialRequest;
use App\Http\Requests\Material\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Service\MaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MaterialController extends Controller
{
  public function __construct(
    private MaterialService $materialService
  ) {}

  public function index(): JsonResponse
  {
    $materials = $this->materialService->findAll();
    return response()->json(MaterialResource::collection($materials));
  }

  public function store(CreateMaterialRequest $request): JsonResponse
  {
    $material = $this->materialService->create($request->validated());
    return response()->json(new MaterialResource($material), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $material = $this->materialService->findOne($id);
    return response()->json(new MaterialResource($material));
  }

  public function update(UpdateMaterialRequest $request, int $id): JsonResponse
  {
    $material = $this->materialService->update($id, $request->validated());
    return response()->json(new MaterialResource($material));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->materialService->delete($id);
    return response()->json([
      'message' => 'Material deleted successfully'
    ], Response::HTTP_OK);
  }
}
