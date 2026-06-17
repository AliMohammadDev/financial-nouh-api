<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Trustee\CreateTrusteeRequest;
use App\Http\Requests\User\Trustee\UpdateTrusteeRequest;
use App\Http\Resources\User\TrusteeResource;
use App\Service\User\TrusteeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TrusteeController extends Controller
{
  public function __construct(
    private TrusteeService $trusteeService
  ) {}

  public function index(): JsonResponse
  {
    $trustees = $this->trusteeService->findAll();
    return response()->json(TrusteeResource::collection($trustees));
  }

  public function store(CreateTrusteeRequest $request): JsonResponse
  {
    $trustee = $this->trusteeService->create($request->validated());
    return response()->json(new TrusteeResource($trustee), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $trustee = $this->trusteeService->findOne($id);
    return response()->json(new TrusteeResource($trustee));
  }

  public function update(UpdateTrusteeRequest $request, int $id): JsonResponse
  {
    $trustee = $this->trusteeService->update($id, $request->validated());
    return response()->json(new TrusteeResource($trustee));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->trusteeService->delete($id);
    return response()->json([
      'message' => 'Trustee deleted successfully'
    ], Response::HTTP_OK);
  }
}
