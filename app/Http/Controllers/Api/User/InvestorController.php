<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Investor\CreateInvestorRequest;
use App\Http\Requests\User\Investor\UpdateInvestorRequest;
use App\Http\Resources\User\InvestorResource;
use App\Service\User\InvestorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InvestorController extends Controller
{
  public function __construct(private InvestorService $investorService) {}

  public function index(): JsonResponse
  {
    return response()->json(InvestorResource::collection($this->investorService->findAll()));
  }

  public function store(CreateInvestorRequest $request): JsonResponse
  {
    $investor = $this->investorService->create($request->validated());
    return response()->json(new InvestorResource($investor), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    return response()->json(new InvestorResource($this->investorService->findOne($id)));
  }

  public function update(UpdateInvestorRequest $request, int $id): JsonResponse
  {
    $investor = $this->investorService->update($id, $request->validated());
    return response()->json(new InvestorResource($investor));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->investorService->delete($id);
    return response()->json(['message' => 'Investor deleted successfully'], Response::HTTP_OK);
  }
}
