<?php

use App\Http\Controllers\Api\EmployeePaymentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FundController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectFundController;
use App\Http\Controllers\Api\User\AdminController;
use App\Http\Controllers\Api\User\ClientController;
use App\Http\Controllers\Api\User\CraftsmenController;
use App\Http\Controllers\Api\User\EmployeeController;
use App\Http\Controllers\Api\User\EngineerController;
use App\Http\Controllers\Api\User\InvestorController;
use App\Http\Controllers\Api\User\SupplierController;
use App\Http\Controllers\Api\User\TrusteeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');


// users
Route::apiResource('clients', ClientController::class);
Route::apiResource('craftsmen', CraftsmenController::class);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('engineers', EngineerController::class);
Route::apiResource('admins', AdminController::class);
Route::apiResource('investors', InvestorController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('trustees', TrusteeController::class);
Route::apiResource('funds', FundController::class);


Route::apiResource('items', ItemController::class);
Route::apiResource('materials', MaterialController::class);


Route::apiResource('projects', ProjectController::class);
Route::apiResource('project-funds', ProjectFundController::class);

Route::apiResource('employee-payments', EmployeePaymentController::class);
Route::apiResource('expenses', ExpenseController::class);
