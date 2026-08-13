<?php

namespace App\Http\Requests\CompanyFund;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DetachCompanyFundRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  /**
   * @return array<string, ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'currency_id' => ['required', 'exists:currencies,id'],
    ];
  }
}
