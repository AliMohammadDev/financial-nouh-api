<?php

namespace App\Http\Requests\User\Trustee;

use App\Http\Trait\User\HasUserValidation;
use App\Models\Trustee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTrusteeRequest extends FormRequest
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
    $trustee = Trustee::find($this->route('trustee'));
    return array_merge($this->userUpdateRules($trustee?->user_id), [
      'kinship_relation' => ['sometimes', 'required', 'string', 'max:255'],
    ]);
  }
}
