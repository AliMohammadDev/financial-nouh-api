<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Service\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{

  public function __construct(
    private ProjectService $projectService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $projects = $this->projectService->findAll($paginate, $perPage, $page);

    return ProjectResource::collection($projects);
  }

  public function store(CreateProjectRequest $request): JsonResponse
  {
    $project = $this->projectService->create($request->validated());
    return response()->json(new ProjectResource($project), Response::HTTP_CREATED);
  }

  public function show(Project $project): JsonResponse
  {
    $projectWithRelations = $this->projectService->findOne($project);
    return response()->json(new ProjectResource($projectWithRelations));
  }

  public function update(UpdateProjectRequest $request, Project $project): JsonResponse
  {
    $updatedProject = $this->projectService->update($project, $request->validated());
    return response()->json(new ProjectResource($updatedProject));
  }

  public function destroy(Project $project): JsonResponse
  {
    $this->projectService->delete($project);
    return response()->json([
      'message' => 'تم حذف المشروع بنجاح'
    ], Response::HTTP_OK);
  }
}
