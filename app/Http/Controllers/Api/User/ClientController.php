<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Client\CreateClientRequest;
use App\Http\Requests\User\Client\UpdateClientRequest;
use App\Http\Resources\User\ClientResource;
use App\Service\User\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ClientController extends Controller
{
  public function __construct(
    private ClientService $clientService
  ) {}

  public function index(): JsonResponse
  {
    $clients = $this->clientService->findAll();
    return response()->json(ClientResource::collection($clients));
  }

  public function store(CreateClientRequest $request): JsonResponse
  {
    $client = $this->clientService->create($request->validated());
    return response()->json(new ClientResource($client), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $client = $this->clientService->findOne($id);
    return response()->json(new ClientResource($client));
  }

  public function update(UpdateClientRequest $request, int $id): JsonResponse
  {
    $client = $this->clientService->update($id, $request->validated());
    return response()->json(new ClientResource($client));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->clientService->delete($id);
    return response()->json([
      'message' => 'Client deleted successfully'
    ], Response::HTTP_OK);
  }
}
