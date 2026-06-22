<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\AttachProjectFundRequest;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectFundResource;
use App\Http\Resources\ProjectResource;
use App\Service\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectController extends Controller
{

  public function __construct(
    private ProjectService $projectService
  ) {}

  public function index(): JsonResponse
  {
    $projects = $this->projectService->findAll();
    return response()->json(ProjectResource::collection($projects));
  }

  public function store(CreateProjectRequest $request): JsonResponse
  {
    $project = $this->projectService->create($request->validated());
    return response()->json(new ProjectResource($project), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $project = $this->projectService->findOne($id);
    return response()->json(new ProjectResource($project));
  }

  public function update(UpdateProjectRequest $request, int $id): JsonResponse
  {
    $project = $this->projectService->update($id, $request->validated());
    return response()->json(new ProjectResource($project));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->projectService->delete($id);
    return response()->json([
      'message' => 'Project deleted successfully'
    ], Response::HTTP_OK);
  }

  public function attachFund(AttachProjectFundRequest $request, int $projectId): JsonResponse
  {
    $projectFund = $this->projectService->attachFund($projectId, $request->validated());
    return response()->json(new ProjectFundResource($projectFund), Response::HTTP_OK);
  }

  public function detachFund(int $projectFundId): JsonResponse
  {
    $this->projectService->detachFund($projectFundId);
    return response()->json([
      'message' => 'Fund detached from project successfully'
    ], Response::HTTP_OK);
  }
}
