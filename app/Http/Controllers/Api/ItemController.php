<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\CreateItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Service\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ItemController extends Controller
{
  public function __construct(
    private ItemService $itemService
  ) {}

  public function index(): JsonResponse
  {
    $items = $this->itemService->findAll();
    return response()->json(ItemResource::collection($items));
  }

  public function store(CreateItemRequest $request): JsonResponse
  {
    $item = $this->itemService->create($request->validated());
    return response()->json(new ItemResource($item), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $item = $this->itemService->findOne($id);
    return response()->json(new ItemResource($item));
  }

  public function update(UpdateItemRequest $request, int $id): JsonResponse
  {
    $item = $this->itemService->update($id, $request->validated());
    return response()->json(new ItemResource($item));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->itemService->delete($id);
    return response()->json([
      'message' => 'Structural item and its materials deleted successfully'
    ], Response::HTTP_OK);
  }
}
