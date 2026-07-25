<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\CreateDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Service\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentController extends Controller
{
  public function __construct(
    private DepartmentService $departmentService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $departments = $this->departmentService->findAll($paginate, $perPage, $page);

    return response()->json(DepartmentResource::collection($departments));
  }

  public function store(CreateDepartmentRequest $request): JsonResponse
  {
    $department = $this->departmentService->create($request->validated());
    return response()->json(new DepartmentResource($department), Response::HTTP_CREATED);
  }

  public function show(Department $department): JsonResponse
  {
    $departmentData = $this->departmentService->findOne($department);
    return response()->json(new DepartmentResource($departmentData));
  }

  public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
  {
    $updatedDepartment = $this->departmentService->update($department, $request->validated());
    return response()->json(new DepartmentResource($updatedDepartment));
  }

  public function destroy(Department $department): JsonResponse
  {
    $this->departmentService->delete($department);
    return response()->json([
      'message' => 'Department deleted successfully'
    ], Response::HTTP_OK);
  }
}
