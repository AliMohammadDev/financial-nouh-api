<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Service\AuditLogService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
  public function __construct(
    private AuditLogService $auditLogService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $filters  = $request->input('filter', []);

    $logs = $this->auditLogService->findAll($paginate, $perPage, $page, $filters);

    return AuditLogResource::collection($logs);
  }
}
