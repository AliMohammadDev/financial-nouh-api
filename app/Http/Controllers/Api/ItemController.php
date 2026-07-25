<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\CreateItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Service\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemController extends Controller
{
  public function __construct(
    private ItemService $itemService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $items = $this->itemService->findAll($paginate, $perPage, $page);

    return ItemResource::collection($items);
  }

  public function store(CreateItemRequest $request): JsonResponse
  {
    $item = $this->itemService->create($request->validated());
    return response()->json(new ItemResource($item), Response::HTTP_CREATED);
  }

  public function show(Item $item): JsonResponse
  {
    $itemWithRelations = $this->itemService->findOne($item);
    return response()->json(new ItemResource($itemWithRelations));
  }

  public function update(UpdateItemRequest $request, Item $item): JsonResponse
  {
    $updatedItem = $this->itemService->update($item, $request->validated());
    return response()->json(new ItemResource($updatedItem));
  }

  public function destroy(Item $item): JsonResponse
  {
    $this->itemService->delete($item);
    return response()->json([
      'message' => 'Structural item and its materials deleted successfully'
    ], Response::HTTP_OK);
  }
}
