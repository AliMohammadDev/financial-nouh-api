<?php

namespace App\Http\Requests\MoneyExchange;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateMoneyExchangeRequest extends FormRequest
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
      'exchangeable_type' => ['required', 'string', 'in:App\Models\CurrencyFund,App\Models\CompanyFundCurrency,App\Models\ProjectFundCurrency'],
      'exchangeable_id'   => ['required', 'integer'],
      'from_currency'     => ['required', 'integer', 'exists:currencies,id'],
      'to_currency'       => ['required', 'integer', 'exists:currencies,id', 'different:from_currency'],
      'amount'            => ['required', 'numeric', 'min:0'],
      'exchange_rate'     => ['required', 'numeric', 'min:0'],
      'operation'         => ['required', 'in:multiply,divide'],
      'exp'               => ['nullable', 'integer'],
    ];
  }
}
