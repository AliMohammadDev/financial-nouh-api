<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProjectFund\CreateProjectFundRequest;
use App\Http\Requests\ProjectFund\UpdateProjectFundRequest;
use App\Http\Resources\ProjectFundResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectFund\AttachProjectFundRequest;
use App\Models\ProjectFund;
use App\Service\ProjectFundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectFundController extends Controller
{
  public function __construct(
    private ProjectFundService $fundService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $funds = $this->fundService->findAll($paginate, $perPage, $page);

    return ProjectFundResource::collection($funds);
  }
  public function store(CreateProjectFundRequest $request): JsonResponse
  {
    $fund = $this->fundService->create($request->validated());
    return response()->json(new ProjectFundResource($fund), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $fund = $this->fundService->findOne($id);
    return response()->json(new ProjectFundResource($fund));
  }

  public function update(UpdateProjectFundRequest $request, int $id): JsonResponse
  {
    $fund = $this->fundService->update($id, $request->validated());
    return response()->json(new ProjectFundResource($fund));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->fundService->delete($id);
    return response()->json([
      'message' => 'Project fund deleted successfully'
    ], Response::HTTP_OK);
  }

  public function attachCurrency(AttachProjectFundRequest $request, ProjectFund $projectFund): JsonResponse
  {
    $updatedFund = $this->fundService->attachCurrency($projectFund, $request->validated());

    return response()->json([
      'message' => 'Currency attached to project fund successfully',
      'data'    => new ProjectFundResource($updatedFund)
    ], Response::HTTP_OK);
  }
}