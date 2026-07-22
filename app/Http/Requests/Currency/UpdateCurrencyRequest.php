<?php

namespace App\Http\Requests\Currency;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
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
    $currencyId = $this->route('currency')?->id ?? $this->route('currency');

    return [
      'currency' => ['sometimes', 'required', 'string', 'max:50', 'unique:currencies,currency,' . $currencyId],
      'symbol'   => ['sometimes', 'required', 'string', 'max:10'],
    ];
  }
}
