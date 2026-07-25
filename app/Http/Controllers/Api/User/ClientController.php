<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Client\CreateClientRequest;
use App\Http\Requests\User\Client\UpdateClientRequest;
use App\Http\Resources\User\ClientResource;
use App\Models\Client;
use App\Service\User\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientController extends Controller
{
  public function __construct(
    private ClientService $clientService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $clients = $this->clientService->findAll($paginate, $perPage, $page);

    return ClientResource::collection($clients);
  }

  public function store(CreateClientRequest $request): JsonResponse
  {
    $client = $this->clientService->create($request->validated());
    return response()->json(new ClientResource($client), Response::HTTP_CREATED);
  }

  public function show(Client $client): JsonResponse
  {
    $clientWithRelations = $this->clientService->findOne($client);
    return response()->json(new ClientResource($clientWithRelations));
  }

  public function update(UpdateClientRequest $request, Client $client): JsonResponse
  {
    $updatedClient = $this->clientService->update($client, $request->validated());
    return response()->json(new ClientResource($updatedClient));
  }

  public function destroy(Client $client): JsonResponse
  {
    $this->clientService->delete($client);
    return response()->json([
      'message' => 'Client deleted successfully'
    ], Response::HTTP_OK);
  }
}
