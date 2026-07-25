<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectTeam\CreateProjectTeamRequest;
use App\Http\Requests\ProjectTeam\UpdateProjectTeamRequest;
use App\Http\Resources\ProjectTeamResource;
use App\Models\ProjectTeam;
use App\Service\ProjectTeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectTeamController extends Controller
{
  public function __construct(
    private ProjectTeamService $projectTeamService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);
    $teams = $this->projectTeamService->findAll($paginate, $perPage, $page);
    return response()->json(ProjectTeamResource::collection($teams));
  }

  public function store(CreateProjectTeamRequest $request): JsonResponse
  {
    $team = $this->projectTeamService->create($request->validated());
    return response()->json(new ProjectTeamResource($team), Response::HTTP_CREATED);
  }

  public function show(ProjectTeam $projectTeam): JsonResponse
  {
    $teamData = $this->projectTeamService->findOne($projectTeam);
    return response()->json(new ProjectTeamResource($teamData));
  }

  public function update(UpdateProjectTeamRequest $request, ProjectTeam $projectTeam): JsonResponse
  {
    $updatedTeam = $this->projectTeamService->update($projectTeam, $request->validated());
    return response()->json(new ProjectTeamResource($updatedTeam));
  }

  public function destroy(ProjectTeam $projectTeam): JsonResponse
  {
    $this->projectTeamService->delete($projectTeam);
    return response()->json([
      'message' => 'Project team member deleted successfully'
    ], Response::HTTP_OK);
  }
}
