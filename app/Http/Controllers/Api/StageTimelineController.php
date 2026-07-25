<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StageTimeline\CreateStageTimelineRequest;
use App\Http\Requests\StageTimeline\UpdateStageTimelineRequest;
use App\Http\Resources\StageTimelineResource;
use App\Models\StageTimeline;
use App\Service\StageTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StageTimelineController extends Controller
{
  public function __construct(
    private StageTimelineService $stageTimelineService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $timelines = $this->stageTimelineService->findAll($paginate, $perPage, $page);

    return StageTimelineResource::collection($timelines)->response();
  }

  public function store(CreateStageTimelineRequest $request): JsonResponse
  {
    $timeline = $this->stageTimelineService->create($request->validated());
    return (new StageTimelineResource($timeline))
      ->response()
      ->setStatusCode(Response::HTTP_CREATED);
  }

  public function show(StageTimeline $stageTimeline): JsonResponse
  {
    $timeline = $this->stageTimelineService->findOne($stageTimeline);
    return (new StageTimelineResource($timeline))->response();
  }

  public function update(UpdateStageTimelineRequest $request, StageTimeline $stageTimeline): JsonResponse
  {
    $timeline = $this->stageTimelineService->update($stageTimeline, $request->validated());
    return (new StageTimelineResource($timeline))->response();
  }

  public function destroy(StageTimeline $stageTimeline): JsonResponse
  {
    $this->stageTimelineService->delete($stageTimeline);

    return response()->json([
      'message' => 'Stage timeline deleted successfully.'
    ], Response::HTTP_OK); 
  }
}