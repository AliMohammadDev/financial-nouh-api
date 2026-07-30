<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CompanyFundController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\EmployeePaymentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FundController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceItemController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectFundController;
use App\Http\Controllers\Api\ProjectStageController;
use App\Http\Controllers\Api\ProjectTeamController;
use App\Http\Controllers\Api\RevenueController;
use App\Http\Controllers\Api\StageTimelineController;
use App\Http\Controllers\Api\User\AdminController;
use App\Http\Controllers\Api\User\ClientController;
use App\Http\Controllers\Api\User\CraftsmenController;
use App\Http\Controllers\Api\User\EmployeeController;
use App\Http\Controllers\Api\User\EngineerController;
use App\Http\Controllers\Api\User\InvestorController;
use App\Http\Controllers\Api\User\SupplierController;
use App\Http\Controllers\Api\User\TrusteeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::post('login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {

  // ==========================================
  // Authentication & User Profile
  // ==========================================
  Route::get('me', [AuthController::class, 'me']);
  Route::post('logout', [AuthController::class, 'logout']);

  // ==========================================
  // Notifications Management
  // ==========================================
  Route::get('/notifications', [NotificationController::class, 'index']);
  Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
  Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

  // ==========================================
  // User Management
  // ==========================================
  Route::apiResource('clients', ClientController::class);
  Route::apiResource('craftsmen', CraftsmenController::class);
  Route::apiResource('employees', EmployeeController::class);
  Route::apiResource('engineers', EngineerController::class);
  Route::apiResource('admins', AdminController::class);
  Route::apiResource('investors', InvestorController::class);
  Route::apiResource('suppliers', SupplierController::class);
  Route::apiResource('trustees', TrusteeController::class);

  // ==========================================
  // Directory & Archive Management
  // ==========================================
  Route::apiResource('directories', DirectoryController::class);
  Route::post('directories/{directory}/files', [DirectoryController::class, 'uploadFile']);
  Route::delete('directories/{directory}/files/{mediaId}', [DirectoryController::class, 'deleteFile']);
  Route::post('directories/move-file', [DirectoryController::class, 'moveFile']);

  // ==========================================
  // System Configurations
  // ==========================================
  Route::apiResource('currencies', CurrencyController::class);

  // ==========================================
  // Project & Structural Management
  // ==========================================
  Route::apiResource('departments', DepartmentController::class);
  Route::apiResource('projects', ProjectController::class);
  Route::apiResource('project-funds', ProjectFundController::class);
  Route::post('project-funds/{projectFund}/currencies', [ProjectFundController::class, 'attachCurrency']);
  Route::apiResource('project-teams', ProjectTeamController::class);
  Route::apiResource('project-stages', ProjectStageController::class);
  Route::apiResource('stage-timelines', StageTimelineController::class);

  // ==========================================
  // Funds Management
  // ==========================================
  Route::apiResource('funds', FundController::class);
  Route::post('funds/{fund}/currencies', [FundController::class, 'attachCurrency']);

  Route::apiResource('company-funds', CompanyFundController::class);
  Route::post('company-funds/{companyFund}/currencies', [CompanyFundController::class, 'attachCurrency']);

  // ==========================================
  // Inventory & Invoicing Management
  // ==========================================
  Route::apiResource('items', ItemController::class);
  Route::apiResource('materials', MaterialController::class);
  Route::apiResource('invoices', InvoiceController::class);
  Route::apiResource('invoice-items', InvoiceItemController::class);

  // ==========================================
  // Financial, Payroll & Audit Logs
  // ==========================================
  Route::apiResource('employee-payments', EmployeePaymentController::class);
  Route::apiResource('revenues', RevenueController::class);
  Route::apiResource('expenses', ExpenseController::class);
  Route::get('/audit-logs', [AuditLogController::class, 'index']);
});
