<?php

namespace App\Service\User;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientService
{
  public function findAll(): Collection
  {
    return Client::with('user')->get();
  }

  public function findOne(int $id): Client
  {
    return Client::with('user')->findOrFail($id);
  }

  public function create(array $data): Client
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $client = Client::create([
        'user_id' => $user->id,
      ]);

      return $client->load('user');
    });
  }

  public function update(int $id, array $data): Client
  {
    $client = Client::findOrFail($id);

    DB::transaction(function () use ($client, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $client->user()->update($data);
    });

    return $client->load('user');
  }

  public function delete(int $id): bool
  {
    $client = Client::findOrFail($id);

    return DB::transaction(function () use ($client) {
      return (bool) $client->user()->delete();
    });
  }
}
