<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone_number', 'address', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
  /** @use HasFactory<UserFactory> */
  use HasApiTokens, HasFactory, Notifiable, HasRoles;

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }
  public function funds(): HasMany
  {
    return $this->hasMany(Fund::class);
  }

  public function admin()
  {
    return $this->hasOne(Admin::class);
  }

  public function client()
  {
    return $this->hasOne(Client::class);
  }

  public function employee()
  {
    return $this->hasOne(Employee::class);
  }

  public function engineer()
  {
    return $this->hasOne(Engineer::class);
  }

  public function craftsmen()
  {
    return $this->hasOne(Craftsmen::class);
  }

  public function supplier()
  {
    return $this->hasOne(Supplier::class);
  }

  public function trustee()
  {
    return $this->hasOne(Trustee::class);
  }
}
