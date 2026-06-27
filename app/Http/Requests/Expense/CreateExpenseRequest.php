<?php

namespace App\Http\Requests\Expense;

use App\Enums\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateExpenseRequest extends FormRequest
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
      'expenseable_type'  => 'required|string|in:App\Models\Fund,App\Models\ProjectFund',
      'expenseable_id'    => 'required|integer',
      'description'         => 'required|string',
      'amount'            => 'required|numeric|min:0',
      'currency' => ['required', new Enum(Currency::class)],
      'is_posted'         => 'nullable|boolean',
      'employee_id'  => 'nullable|exists:employees,id',
      'user_id'  => 'required|exists:users,id',

    ];
  }
}
