<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Employee\CreateEmployeeRequest;
use App\Http\Requests\User\Employee\UpdateEmployeeRequest;
use App\Http\Resources\User\EmployeeResource;
use App\Service\User\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
  public function __construct(
    private EmployeeService $employeeService
  ) {}

  public function index(): JsonResponse
  {
    $employees = $this->employeeService->findAll();
    return response()->json(EmployeeResource::collection($employees));
  }

  public function store(CreateEmployeeRequest $request): JsonResponse
  {
    $employee = $this->employeeService->create($request->validated());
    return response()->json(new EmployeeResource($employee), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $employee = $this->employeeService->findOne($id);
    return response()->json(new EmployeeResource($employee));
  }

  public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
  {
    $employee = $this->employeeService->update($id, $request->validated());
    return response()->json(new EmployeeResource($employee));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->employeeService->delete($id);
    return response()->json([
      'message' => 'Employee deleted successfully'
    ], Response::HTTP_OK);
  }
}
