<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\EmployeePaymentController;
use App\Http\Controllers\Api\CompanyFundController; // <-- إضافة الـ Controller
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\User\CraftsmenController;
use App\Http\Controllers\Api\User\EmployeeController;
use App\Http\Controllers\Api\User\EngineerController;
use App\Http\Controllers\Api\User\InvestorController;
use App\Http\Controllers\Api\User\SupplierController;
use App\Http\Controllers\Api\User\TrusteeController;
use App\Http\Controllers\Api\ProjectFundController;
use App\Http\Controllers\Api\User\ClientController;
use App\Http\Controllers\Api\User\AdminController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\FundController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceItemController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectStageController;
use App\Http\Controllers\Api\ProjectTeamController;
use App\Http\Controllers\Api\RevenueController;
use App\Http\Controllers\Api\StageTimelineController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



Route::post('login', [AuthController::class, 'login']);



Route::middleware(['auth:sanctum'])->group(function () {
  // User
  Route::get('me', [AuthController::class, 'me']);
  Route::post('logout', [AuthController::class, 'logout']);

  // Notifications
  Route::get('/notifications', [NotificationController::class, 'index']);
  Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
  Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);



  // ==========================================
  // User Management Routes
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
  // Project & Fund Management
  // ==========================================
  Route::apiResource('departments', DepartmentController::class);


  Route::apiResource('projects', ProjectController::class);
  Route::apiResource('project-funds', ProjectFundController::class);
  Route::post('project-funds/{projectFund}/currencies', [ProjectFundController::class, 'attachCurrency']);
  Route::apiResource('project-teams', ProjectTeamController::class);
  Route::apiResource('project-stages', ProjectStageController::class);
  Route::apiResource('stage-timelines', StageTimelineController::class);
});






// Fund Management Routes
Route::apiResource('funds', FundController::class);
Route::post('funds/{fund}/currencies', [FundController::class, 'attachCurrency']);


// ==========================================
// Inventory & Structural Management
// ==========================================
Route::apiResource('items', ItemController::class);
Route::apiResource('materials', MaterialController::class);




Route::apiResource('invoices', InvoiceController::class);
Route::apiResource('invoice-items', InvoiceItemController::class);


// ==========================================
// Company Funds Management
// ==========================================
Route::apiResource('company-funds', CompanyFundController::class);
Route::post('company-funds/{companyFund}/currencies', [CompanyFundController::class, 'attachCurrency']);


// ==========================================
// Financial & Payroll Management
// ==========================================
Route::apiResource('employee-payments', EmployeePaymentController::class);
Route::apiResource('revenues', RevenueController::class);
Route::apiResource('expenses', ExpenseController::class);


// ==========================================
// System Configurations
// ==========================================
Route::apiResource('currencies', CurrencyController::class);




Route::get('/audit-logs', [AuditLogController::class, 'index']);
