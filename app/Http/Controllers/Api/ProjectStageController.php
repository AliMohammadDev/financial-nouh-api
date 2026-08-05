<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectStage\CreateProjectStageRequest;
use App\Http\Requests\ProjectStage\UpdateProjectStageRequest;
use App\Http\Resources\ProjectStageResource;
use App\Models\ProjectStage;
use App\Service\ProjectStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectStageController extends Controller
{
  public function __construct(
    private ProjectStageService $projectStageService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $stages = $this->projectStageService->findAll($paginate, $perPage, $page);

    return ProjectStageResource::collection($stages)->response();
  }

  public function store(CreateProjectStageRequest $request): JsonResponse
  {
    $stage = $this->projectStageService->create($request->validated());
    return (new ProjectStageResource($stage))
      ->response()
      ->setStatusCode(Response::HTTP_CREATED);
  }

  public function show(ProjectStage $projectStage): JsonResponse
  {
    $stage = $this->projectStageService->findOne($projectStage);
    return (new ProjectStageResource($stage))->response();
  }

  public function update(UpdateProjectStageRequest $request, ProjectStage $projectStage): JsonResponse
  {
    $stage = $this->projectStageService->update($projectStage, $request->validated());
    return (new ProjectStageResource($stage))->response();
  }

  public function destroy(ProjectStage $projectStage): JsonResponse
  {
    $this->projectStageService->delete($projectStage);

    return response()->json([
      'message' => 'تم حذف مرحلة المشروع بنجاح.'
    ], Response::HTTP_OK);
  }
}
