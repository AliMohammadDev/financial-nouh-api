<?php

namespace App\Http\Resources\User;

use App\Http\Resources\FundResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'           => $this->id,
      'name'         => $this->name,
      'email'        => $this->email,
      'phone_number' => $this->phone_number,
      'address'      => $this->address,

      'role_type'    => $this->getRoleType(),
      'role_details' => $this->getRoleDetails(),

      'funds'        => FundResource::collection($this->whenLoaded('funds')),
      'created_at'   => $this->created_at?->format('Y-m-d'),
    ];
  }

  private function getRoleType(): string
  {
    if ($this->relationLoaded('admin') && $this->admin) return 'admin';
    if ($this->relationLoaded('client') && $this->client) return 'client';
    if ($this->relationLoaded('employee') && $this->employee) return 'employee';
    if ($this->relationLoaded('engineer') && $this->engineer) return 'engineer';
    if ($this->relationLoaded('craftsmen') && $this->craftsmen) return 'craftsmen';
    if ($this->relationLoaded('supplier') && $this->supplier) return 'supplier';
    if ($this->relationLoaded('trustee') && $this->trustee) return 'trustee';
    if ($this->relationLoaded('investor') && $this->investor) return 'investor';
    if ($this->relationLoaded('dailyWorker') && $this->dailyWorker) return 'daily_worker';

    return 'user';
  }

  private function getRoleDetails()
  {
    if ($this->relationLoaded('admin') && $this->admin) return $this->admin;
    if ($this->relationLoaded('client') && $this->client) return $this->client;
    if ($this->relationLoaded('employee') && $this->employee) return $this->employee;
    if ($this->relationLoaded('engineer') && $this->engineer) return $this->engineer;
    if ($this->relationLoaded('craftsmen') && $this->craftsmen) return $this->craftsmen;
    if ($this->relationLoaded('supplier') && $this->supplier) return $this->supplier;
    if ($this->relationLoaded('trustee') && $this->trustee) return $this->trustee;
    if ($this->relationLoaded('investor') && $this->investor) return $this->investor;
    if ($this->relationLoaded('dailyWorker') && $this->dailyWorker) return $this->dailyWorker;

    return null;
  }
}
