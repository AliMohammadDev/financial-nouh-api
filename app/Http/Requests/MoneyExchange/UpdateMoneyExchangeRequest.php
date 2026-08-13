<?php

namespace App\Http\Requests\MoneyExchange;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMoneyExchangeRequest extends FormRequest
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
      'exchangeable_type' => ['sometimes', 'string', 'in:App\Models\CurrencyFund,App\Models\CompanyFundCurrency,App\Models\ProjectFundCurrency'],
      'exchangeable_id'   => ['sometimes', 'integer'],
      'from_currency'     => ['sometimes', 'integer', 'exists:currencies,id'],
      'to_currency'       => ['sometimes', 'integer', 'exists:currencies,id', 'different:from_currency'],
      'amount'            => ['sometimes', 'numeric', 'min:0'],
      'exchange_rate'     => ['sometimes', 'numeric', 'min:0'],
      'converted_amount' => ['sometimes', 'numeric', 'min:0'],
      'operation'         => ['sometimes', 'in:multiply,divide'],
      'exp'               => ['nullable', 'integer'],
    ];
  }
}
