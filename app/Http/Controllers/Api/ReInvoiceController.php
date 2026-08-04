<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReInvoice\CreateReInvoiceRequest;
use App\Http\Requests\ReInvoice\UpdateReInvoiceRequest;
use App\Http\Resources\ReInvoiceResource;
use App\Models\ReInvoice;
use App\Service\ReInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReInvoiceController extends Controller
{
  public function __construct(
    private ReInvoiceService $reInvoiceService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $reInvoices = $this->reInvoiceService->findAll($paginate, $perPage, $page);

    return ReInvoiceResource::collection($reInvoices);
  }

  public function store(CreateReInvoiceRequest $request): JsonResponse
  {
    $reInvoice = $this->reInvoiceService->create($request->validated());
    return response()->json(new ReInvoiceResource($reInvoice), Response::HTTP_CREATED);
  }

  public function show(ReInvoice $reInvoice): JsonResponse
  {
    $reInvoiceWithRelations = $this->reInvoiceService->findOne($reInvoice);
    return response()->json(new ReInvoiceResource($reInvoiceWithRelations));
  }

  public function update(UpdateReInvoiceRequest $request, ReInvoice $reInvoice): JsonResponse
  {
    $updatedReInvoice = $this->reInvoiceService->update($reInvoice, $request->validated());
    return response()->json(new ReInvoiceResource($updatedReInvoice));
  }

  public function destroy(ReInvoice $reInvoice): JsonResponse
  {
    $this->reInvoiceService->delete($reInvoice);
    return response()->json([
      'message' => 'ReInvoice deleted successfully'
    ], Response::HTTP_OK);
  }
}
