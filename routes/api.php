<?php

use App\Http\Controllers\Api\FundController;
use App\Http\Controllers\Api\User\ClientController;
use App\Http\Controllers\Api\User\CraftsmenController;
use App\Http\Controllers\Api\User\EmployeeController;
use App\Http\Controllers\Api\User\EngineerController;
use App\Http\Controllers\Api\User\SupplierController;
use App\Http\Controllers\Api\User\TrusteeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');



Route::apiResource('clients', ClientController::class);

Route::apiResource('craftsmen', CraftsmenController::class);

Route::apiResource('employees', EmployeeController::class);

Route::apiResource('engineers', EngineerController::class);

Route::apiResource('suppliers', SupplierController::class);

Route::apiResource('trustees', TrusteeController::class);


Route::apiResource('funds', FundController::class);
