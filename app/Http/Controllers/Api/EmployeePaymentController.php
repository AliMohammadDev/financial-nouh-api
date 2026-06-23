<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePayment\CreateEmployeePaymentRequest;
use App\Http\Requests\EmployeePayment\UpdateEmployeePaymentRequest;
use App\Http\Resources\EmployeePaymentResource;
use App\Service\EmployeePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EmployeePaymentController extends Controller
{
  public function __construct(
    private EmployeePaymentService $paymentService
  ) {}

  public function index(): JsonResponse
  {
    $payments = $this->paymentService->findAll();
    return response()->json(EmployeePaymentResource::collection($payments));
  }

  public function store(CreateEmployeePaymentRequest $request): JsonResponse
  {
    $payment = $this->paymentService->create($request->validated());
    return response()->json(new EmployeePaymentResource($payment), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $payment = $this->paymentService->findOne($id);
    return response()->json(new EmployeePaymentResource($payment));
  }

  public function update(UpdateEmployeePaymentRequest $request, int $id): JsonResponse
  {
    $payment = $this->paymentService->update($id, $request->validated());
    return response()->json(new EmployeePaymentResource($payment));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->paymentService->delete($id);
    return response()->json([
      'message' => 'Employee payment deleted successfully'
    ], Response::HTTP_OK);
  }
}
