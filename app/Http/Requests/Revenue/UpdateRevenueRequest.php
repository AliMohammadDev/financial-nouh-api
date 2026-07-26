<?php

namespace App\Http\Requests\Revenue;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRevenueRequest extends FormRequest
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
      'revenueable_type' => ['sometimes', 'string', 'in:App\Models\CurrencyFund,App\Models\CompanyFundCurrency,App\Models\ProjectFundCurrency'],
      'revenueable_id'   => ['sometimes', 'integer'],
      'statement'        => ['sometimes', 'string'],
      'amount'           => ['sometimes', 'numeric', 'min:0.01'],
      'is_posted'        => ['nullable', 'boolean'],
      'user_id'          => ['sometimes', 'exists:users,id'],
      'received_by'      => ['sometimes', 'exists:users,id'],
    ];
  }
}
