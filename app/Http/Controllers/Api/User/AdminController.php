<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Admin\CreateAdminRequest;
use App\Http\Requests\User\Admin\UpdateAdminRequest;
use App\Http\Resources\User\AdminResource;
use App\Service\User\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AdminController extends Controller
{
  public function __construct(private AdminService $adminService) {}

  public function index(): JsonResponse
  {
    return response()->json(AdminResource::collection($this->adminService->findAll()));
  }

  public function store(CreateAdminRequest $request): JsonResponse
  {
    $admin = $this->adminService->create($request->validated());
    return response()->json(new AdminResource($admin), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    return response()->json(new AdminResource($this->adminService->findOne($id)));
  }

  public function update(UpdateAdminRequest $request, int $id): JsonResponse
  {
    $admin = $this->adminService->update($id, $request->validated());
    return response()->json(new AdminResource($admin));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->adminService->delete($id);
    return response()->json(['message' => 'Admin deleted successfully'], Response::HTTP_OK);
  }
}
