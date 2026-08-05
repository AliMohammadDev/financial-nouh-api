<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Material\CreateMaterialRequest;
use App\Http\Requests\Material\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Service\MaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MaterialController extends Controller
{
  public function __construct(
    private MaterialService $materialService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $materials = $this->materialService->findAll($paginate, $perPage, $page);

    return MaterialResource::collection($materials);
  }

  public function store(CreateMaterialRequest $request): JsonResponse
  {
    $material = $this->materialService->create($request->validated());
    return response()->json(new MaterialResource($material), Response::HTTP_CREATED);
  }

  public function show(Material $material): JsonResponse
  {
    $materialWithRelations = $this->materialService->findOne($material);
    return response()->json(new MaterialResource($materialWithRelations));
  }

  public function update(UpdateMaterialRequest $request, Material $material): JsonResponse
  {
    $updatedMaterial = $this->materialService->update($material, $request->validated());
    return response()->json(new MaterialResource($updatedMaterial));
  }

  public function destroy(Material $material): JsonResponse
  {
    $this->materialService->delete($material);
    return response()->json([
      'message' => 'تم حذف المادة بنجاح'
    ], Response::HTTP_OK);
  }
}
