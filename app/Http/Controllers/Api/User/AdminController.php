<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Admin\CreateAdminRequest;
use App\Http\Requests\User\Admin\UpdateAdminRequest;
use App\Http\Resources\User\AdminResource;
use App\Models\Admin;
use App\Service\User\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminController extends Controller
{
  public function __construct(private AdminService $adminService) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $admins = $this->adminService->findAll($paginate, $perPage, $page);

    return AdminResource::collection($admins);
  }

  public function store(CreateAdminRequest $request): JsonResponse
  {
    $admin = $this->adminService->create(
      $request->validated(),
      $request->file('images')
    );
    return response()->json(new AdminResource($admin), Response::HTTP_CREATED);
  }

  public function show(Admin $admin): JsonResponse
  {
    $adminWithRelations = $this->adminService->findOne($admin);
    return response()->json(new AdminResource($adminWithRelations));
  }

  public function update(UpdateAdminRequest $request, Admin $admin): JsonResponse
  {
    $deletedMediaIds = $request->input('deleted_media_ids', []);

    $updatedAdmin = $this->adminService->update(
      $admin,
      $request->validated(),
      $request->file('images'),
      $deletedMediaIds
    );

    return response()->json(new AdminResource($updatedAdmin));
  }

  public function destroy(Admin $admin): JsonResponse
  {
    $this->adminService->delete($admin);
    return response()->json(['message' => 'Admin deleted successfully'], Response::HTTP_OK);
  }
}
