<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\MediaLibrary\UserPathGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone_number', 'address', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable  implements HasMedia
{
  /** @use HasFactory<UserFactory> */
  use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;

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

  protected static function booting(): void
  {
    PathGeneratorFactory::setCustomPathGenerators(
      static::class,
      UserPathGenerator::class
    );
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
  public function investor()
  {
    return $this->hasOne(Investor::class);
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

  public function dailyWorker()
  {
    return $this->hasOne(DailyWorker::class);
  }
}
