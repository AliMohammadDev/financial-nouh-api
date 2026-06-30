<?php

namespace App\Http\Requests\User\Investor;

use App\Http\Trait\User\HasUserValidation;
use App\Models\Investor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvestorRequest extends FormRequest
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
    $investor = Investor::find($this->route('investor'));
    return array_merge($this->userUpdateRules($investor?->user_id), [
      'investment_ratio' => 'sometimes|required|numeric|min:0|max:100',
    ]);
  }
}
