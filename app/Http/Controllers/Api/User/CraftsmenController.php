<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Craftsmen\CreateCraftsmenRequest;
use App\Http\Requests\User\Craftsmen\UpdateCraftsmenRequest;
use App\Http\Resources\User\CraftsmenResource;
use App\Service\User\CraftsmenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CraftsmenController extends Controller
{
  public function __construct(
    private CraftsmenService $craftsmenService
  ) {}

  public function index(): JsonResponse
  {
    $craftsmen = $this->craftsmenService->findAll();
    return response()->json(CraftsmenResource::collection($craftsmen));
  }

  public function store(CreateCraftsmenRequest $request): JsonResponse
  {
    $craftsman = $this->craftsmenService->create($request->validated());
    return response()->json(new CraftsmenResource($craftsman), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $craftsman = $this->craftsmenService->findOne($id);
    return response()->json(new CraftsmenResource($craftsman));
  }

  public function update(UpdateCraftsmenRequest $request, int $id): JsonResponse
  {
    $craftsman = $this->craftsmenService->update($id, $request->validated());
    return response()->json(new CraftsmenResource($craftsman));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->craftsmenService->delete($id);
    return response()->json([
      'message' => 'Craftsman deleted successfully'
    ], Response::HTTP_OK);
  }
}
