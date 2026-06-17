<?php

namespace App\Http\Requests\User\Craftsmen;

use App\Http\Trait\User\HasUserValidation;
use App\Models\Craftsmen;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCraftsmenRequest extends FormRequest
{
  use HasUserValidation;

  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $craftsman = Craftsmen::find($this->route('craftsman'));
    return array_merge($this->userUpdateRules($craftsman?->user_id), []);
  }
}
