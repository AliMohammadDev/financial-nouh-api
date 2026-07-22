<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Employee\CreateEmployeeRequest;
use App\Http\Requests\User\Employee\UpdateEmployeeRequest;
use App\Http\Resources\User\EmployeeResource;
use App\Models\Employee;
use App\Service\User\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
  public function __construct(
    private EmployeeService $employeeService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $employees = $this->employeeService->findAll($paginate, $perPage, $page);

    return response()->json(EmployeeResource::collection($employees));
  }

  public function store(CreateEmployeeRequest $request): JsonResponse
  {
    $employee = $this->employeeService->create($request->validated());
    return response()->json(new EmployeeResource($employee), Response::HTTP_CREATED);
  }

  public function show(Employee $employee): JsonResponse
  {
    $employeeWithRelations = $this->employeeService->findOne($employee);
    return response()->json(new EmployeeResource($employeeWithRelations));
  }

  public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
  {
    $updatedEmployee = $this->employeeService->update($employee, $request->validated());
    return response()->json(new EmployeeResource($updatedEmployee));
  }

  public function destroy(Employee $employee): JsonResponse
  {
    $this->employeeService->delete($employee);
    return response()->json([
      'message' => 'Employee deleted successfully'
    ], Response::HTTP_OK);
  }
}
