<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceItem\CreateInvoiceItemRequest;
use App\Http\Requests\InvoiceItem\UpdateInvoiceItemRequest;
use App\Http\Resources\InvoiceItemResource;
use App\Models\InvoiceItem;
use App\Service\InvoiceItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceItemController extends Controller
{
  public function __construct(
    private InvoiceItemService $invoiceItemService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $invoiceItems = $this->invoiceItemService->findAll($paginate, $perPage, $page);

    return InvoiceItemResource::collection($invoiceItems);
  }

  public function store(CreateInvoiceItemRequest $request): JsonResponse
  {
    $invoiceItem = $this->invoiceItemService->create($request->validated());
    return response()->json(new InvoiceItemResource($invoiceItem), Response::HTTP_CREATED);
  }

  public function show(InvoiceItem $invoiceItem): JsonResponse
  {
    $item = $this->invoiceItemService->findOne($invoiceItem);
    return response()->json(new InvoiceItemResource($item));
  }

  public function update(UpdateInvoiceItemRequest $request, InvoiceItem $invoiceItem): JsonResponse
  {
    $updatedItem = $this->invoiceItemService->update($invoiceItem, $request->validated());
    return response()->json(new InvoiceItemResource($updatedItem));
  }

  public function destroy(InvoiceItem $invoiceItem): JsonResponse
  {
    $this->invoiceItemService->delete($invoiceItem);
    return response()->json([
      'message' => 'تم حذف بند الفاتورة بنجاح'
    ], Response::HTTP_OK);
  }
}
