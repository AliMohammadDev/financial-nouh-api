<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Trustee\CreateTrusteeRequest;
use App\Http\Requests\User\Trustee\UpdateTrusteeRequest;
use App\Http\Resources\User\TrusteeResource;
use App\Models\Trustee;
use App\Service\User\TrusteeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrusteeController extends Controller
{
  public function __construct(
    private TrusteeService $trusteeService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $trustees = $this->trusteeService->findAll($paginate, $perPage, $page);

    return  TrusteeResource::collection($trustees);
  }

  public function store(CreateTrusteeRequest $request): JsonResponse
  {
    $trustee = $this->trusteeService->create(
      $request->validated(),
      $request->file('images')
    );
    return response()->json(new TrusteeResource($trustee), Response::HTTP_CREATED);
  }

  public function show(Trustee $trustee): JsonResponse
  {
    $trusteeWithRelations = $this->trusteeService->findOne($trustee);
    return response()->json(new TrusteeResource($trusteeWithRelations));
  }

  public function update(UpdateTrusteeRequest $request, Trustee $trustee): JsonResponse
  {
    $deletedMediaIds = $request->input('deleted_media_ids', []);

    $updatedTrustee = $this->trusteeService->update(
      $trustee,
      $request->validated(),
      $request->file('images'),
      $deletedMediaIds
    );

    return response()->json(new TrusteeResource($updatedTrustee));
  }

  public function destroy(Trustee $trustee): JsonResponse
  {
    $this->trusteeService->delete($trustee);
    return response()->json([
      'message' => 'Trustee deleted successfully'
    ], Response::HTTP_OK);
  }
}
