<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\BulkUpdateIsPostedRequest;
use App\Http\Requests\Invoice\CreateInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Service\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
  public function __construct(
    private InvoiceService $invoiceService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $invoices = $this->invoiceService->findAll($paginate, $perPage, $page);

    return InvoiceResource::collection($invoices);
  }

  public function store(CreateInvoiceRequest $request): JsonResponse
  {
    $invoice = $this->invoiceService->create($request->validated());
    return response()->json(new InvoiceResource($invoice), Response::HTTP_CREATED);
  }

  public function show(Invoice $invoice): JsonResponse
  {
    $invoiceWithRelations = $this->invoiceService->findOne($invoice);
    return response()->json(new InvoiceResource($invoiceWithRelations));
  }

  public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
  {
    $updatedInvoice = $this->invoiceService->update($invoice, $request->validated());
    return response()->json(new InvoiceResource($updatedInvoice));
  }

  public function destroy(Invoice $invoice): JsonResponse
  {
    $this->invoiceService->delete($invoice);
    return response()->json([
      'message' => 'تم حذف  الفاتورة بنجاح'
    ], Response::HTTP_OK);
  }

  public function bulkUpdateIsPosted(BulkUpdateIsPostedRequest $request): JsonResponse
  {
    $count = $this->invoiceService->bulkUpdateIsPosted(
      $request->input('ids'),
      $request->boolean('is_posted')
    );

    return response()->json([
      'message' => "تم تحديث حالة الترحيل لـ {$count} فاتورة بنجاح",
      'updated_count' => $count
    ], Response::HTTP_OK);
  }
}
