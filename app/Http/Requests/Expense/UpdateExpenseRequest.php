<?php

namespace App\Http\Requests\Expense;

use App\Enums\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateExpenseRequest extends FormRequest
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
      'expenseable_type'  => 'sometimes|string|in:App\Models\Fund,App\Models\ProjectFund',
      'expenseable_id'    => 'sometimes|integer',
      'description'         => 'sometimes|string',
      'amount'            => 'sometimes|numeric|min:0',
      'currency' => ['sometimes', new Enum(Currency::class)],
      'is_posted'         => 'nullable|boolean',
      'employee_id'  => 'sometimes|exists:employees,id',
      'user_id'  => 'sometimes|exists:users,id',
    ];
  }
}
