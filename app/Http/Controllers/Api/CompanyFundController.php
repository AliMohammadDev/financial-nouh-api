<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyFund\AttachCompanyFundRequest;
use App\Http\Requests\CompanyFund\CreateCompanyFundRequest;
use App\Http\Requests\CompanyFund\DetachCompanyFundRequest;
use App\Http\Requests\CompanyFund\UpdateCompanyFundRequest;
use App\Http\Resources\CompanyFundResource;
use App\Models\CompanyFund;
use App\Service\CompanyFundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyFundController extends Controller
{
  public function __construct(
    private CompanyFundService $companyFundService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $funds = $this->companyFundService->findAll($paginate, $perPage, $page);

    return CompanyFundResource::collection($funds);
  }

  public function store(CreateCompanyFundRequest $request): JsonResponse
  {
    $fund = $this->companyFundService->create($request->validated());
    return response()->json(new CompanyFundResource($fund), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $fund = $this->companyFundService->findOne($id);
    return response()->json(new CompanyFundResource($fund));
  }

  public function update(UpdateCompanyFundRequest $request, int $id): JsonResponse
  {
    $fund = $this->companyFundService->update($id, $request->validated());
    return response()->json(new CompanyFundResource($fund));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->companyFundService->delete($id);
    return response()->json([
      'message' => 'تم حذف صندوق الشركة بنجاح'
    ], Response::HTTP_OK);
  }

  public function attachCurrency(AttachCompanyFundRequest $request, CompanyFund $companyFund): JsonResponse
  {
    $updatedFund = $this->companyFundService->attachCurrency($companyFund, $request->validated());

    return response()->json([
      'message' => 'تم ربط العملة بالصندوق الشركة بنجاح',
      'data'    => new CompanyFundResource($updatedFund)
    ], Response::HTTP_OK);
  }


  public function detachCurrency(DetachCompanyFundRequest $request, CompanyFund $companyFund): JsonResponse
  {
    $updatedFund = $this->companyFundService->detachCurrency($companyFund, $request->validated());

    return response()->json([
      'message' => 'تم فك ارتباط العملة عن صندوق الشركة بنجاح',
      'data'    => new CompanyFundResource($updatedFund)
    ], Response::HTTP_OK);
  }
}
