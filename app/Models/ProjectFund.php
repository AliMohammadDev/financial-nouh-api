<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['project_id', 'name'])]
class ProjectFund extends Model
{
  use HasFactory;

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function expenses(): MorphMany
  {
    return $this->morphMany(Expense::class, 'expenseable');
  }

  public function currencies(): BelongsToMany
  {
    return $this->belongsToMany(Currency::class, 'project_fund_currencies')
      ->using(ProjectFundCurrency::class)
      ->withPivot(['id', 'balance'])
      ->withTimestamps();
  }
}
