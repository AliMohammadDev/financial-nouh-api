<?php

namespace App\Http\Requests\Fund;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateFundRequest extends FormRequest
{
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
    return [
      'user_id' => 'required|exists:users,id',
      'name'    => 'required|string|max:255',
      'balance_usd' => 'nullable|numeric|min:0',
      'balance_syp' => 'nullable|numeric|min:0',
    ];
  }
}
