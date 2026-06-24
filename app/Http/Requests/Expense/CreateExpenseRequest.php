<?php

namespace App\Http\Requests\Expense;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
      'is_posted'         => 'nullable|boolean',
      'employee_id'  => 'required|exists:employees,id',
      'user_id'  => 'required|exists:users,id',

    ];
  }
}
