<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePayment\CreateEmployeePaymentRequest;
use App\Http\Requests\EmployeePayment\UpdateEmployeePaymentRequest;
use App\Http\Resources\EmployeePaymentResource;
use App\Models\EmployeePayment;
use App\Service\EmployeePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeePaymentController extends Controller
{
  public function __construct(
    private EmployeePaymentService $paymentService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $payments = $this->paymentService->findAll($paginate, $perPage, $page);

    return response()->json(EmployeePaymentResource::collection($payments));
  }

  public function store(CreateEmployeePaymentRequest $request): JsonResponse
  {
    $payment = $this->paymentService->create($request->validated());
    return response()->json(new EmployeePaymentResource($payment), Response::HTTP_CREATED);
  }

  public function show(EmployeePayment $employeePayment): JsonResponse
  {
    $paymentWithRelations = $this->paymentService->findOne($employeePayment);
    return response()->json(new EmployeePaymentResource($paymentWithRelations));
  }

  public function update(UpdateEmployeePaymentRequest $request, EmployeePayment $employeePayment): JsonResponse
  {
    $updatedPayment = $this->paymentService->update($employeePayment, $request->validated());
    return response()->json(new EmployeePaymentResource($updatedPayment));
  }

  public function destroy(EmployeePayment $employeePayment): JsonResponse
  {
    $this->paymentService->delete($employeePayment);
    return response()->json([
      'message' => 'Employee payment deleted successfully'
    ], Response::HTTP_OK);
  }
}
