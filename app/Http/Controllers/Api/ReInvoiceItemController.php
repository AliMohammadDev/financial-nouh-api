<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReInvoiceItem\CreateReInvoiceItemRequest;
use App\Http\Requests\ReInvoiceItem\UpdateReInvoiceItemRequest;
use App\Http\Resources\ReInvoiceItemResource;
use App\Models\ReInvoiceItem;
use App\Service\ReInvoiceItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReInvoiceItemController extends Controller
{
  public function __construct(
    private ReInvoiceItemService $reInvoiceItemService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $reInvoiceItems = $this->reInvoiceItemService->findAll($paginate, $perPage, $page);

    return ReInvoiceItemResource::collection($reInvoiceItems);
  }

  public function store(CreateReInvoiceItemRequest $request): JsonResponse
  {
    $reInvoiceItem = $this->reInvoiceItemService->create($request->validated());
    return response()->json(new ReInvoiceItemResource($reInvoiceItem), Response::HTTP_CREATED);
  }

  public function show(ReInvoiceItem $reInvoiceItem): JsonResponse
  {
    $item = $this->reInvoiceItemService->findOne($reInvoiceItem);
    return response()->json(new ReInvoiceItemResource($item));
  }

  public function update(UpdateReInvoiceItemRequest $request, ReInvoiceItem $reInvoiceItem): JsonResponse
  {
    $updatedItem = $this->reInvoiceItemService->update($reInvoiceItem, $request->validated());
    return response()->json(new ReInvoiceItemResource($updatedItem));
  }


  public function destroy(ReInvoiceItem $reInvoiceItem): JsonResponse
  {
    $this->reInvoiceItemService->delete($reInvoiceItem);
    return response()->json([
      'message' => 'تم حذف بند مرتجع الفاتورة  بنجاح'
    ], Response::HTTP_OK);
  }
}
