<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Investor\CreateInvestorRequest;
use App\Http\Requests\User\Investor\UpdateInvestorRequest;
use App\Http\Resources\User\InvestorResource;
use App\Models\Investor;
use App\Service\User\InvestorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvestorController extends Controller
{
  public function __construct(private InvestorService $investorService) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $investors = $this->investorService->findAll($paginate, $perPage, $page);

    return InvestorResource::collection($investors);
  }

  public function store(CreateInvestorRequest $request): JsonResponse
  {
    $investor = $this->investorService->create($request->validated());
    return response()->json(new InvestorResource($investor), Response::HTTP_CREATED);
  }

  public function show(Investor $investor): JsonResponse
  {
    $investorWithRelations = $this->investorService->findOne($investor);
    return response()->json(new InvestorResource($investorWithRelations));
  }

  public function update(UpdateInvestorRequest $request, Investor $investor): JsonResponse
  {
    $updatedInvestor = $this->investorService->update($investor, $request->validated());
    return response()->json(new InvestorResource($updatedInvestor));
  }

  public function destroy(Investor $investor): JsonResponse
  {
    $this->investorService->delete($investor);
    return response()->json(['message' => 'Investor deleted successfully'], Response::HTTP_OK);
  }
}
