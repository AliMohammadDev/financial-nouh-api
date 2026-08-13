<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Craftsmen\CreateCraftsmenRequest;
use App\Http\Requests\User\Craftsmen\UpdateCraftsmenRequest;
use App\Http\Resources\User\CraftsmenResource;
use App\Models\Craftsmen;
use App\Service\User\CraftsmenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CraftsmenController extends Controller
{
  public function __construct(
    private CraftsmenService $craftsmenService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $craftsmen = $this->craftsmenService->findAll($paginate, $perPage, $page);

    return CraftsmenResource::collection($craftsmen);
  }

  public function store(CreateCraftsmenRequest $request): JsonResponse
  {
    $craftsman = $this->craftsmenService->create(
      $request->validated(),
      $request->file('images')
    );
    return response()->json(new CraftsmenResource($craftsman), Response::HTTP_CREATED);
  }

  public function show(Craftsmen $craftsman): JsonResponse
  {
    $craftsmanWithRelations = $this->craftsmenService->findOne($craftsman);
    return response()->json(new CraftsmenResource($craftsmanWithRelations));
  }

  public function update(UpdateCraftsmenRequest $request, Craftsmen $craftsman): JsonResponse
  {
    $deletedMediaIds = $request->input('deleted_media_ids', []);

    $updatedCraftsman = $this->craftsmenService->update(
      $craftsman,
      $request->validated(),
      $request->file('images'),
      $deletedMediaIds
    );

    return response()->json(new CraftsmenResource($updatedCraftsman));
  }
  public function destroy(Craftsmen $craftsman): JsonResponse
  {
    $this->craftsmenService->delete($craftsman);
    return response()->json([
      'message' => 'Craftsman deleted successfully'
    ], Response::HTTP_OK);
  }
}
