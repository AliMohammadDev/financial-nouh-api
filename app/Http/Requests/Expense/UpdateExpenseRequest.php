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
      'expenseable_type' => ['sometimes', 'string', 'in:App\Models\CurrencyFund,App\Models\CompanyFundCurrency,App\Models\ProjectFundCurrency'],
      'expenseable_id'   => ['sometimes', 'integer'],
      'description'      => ['sometimes', 'string'],
      'amount'           => [
        'sometimes',
        'numeric',
        'min:0.01',
        function ($attribute, $value, $fail) {
          $expense = $this->route('expense');

          if (!$expense) {
            return;
          }

          $modelType = $this->input('expenseable_type', $expense->expenseable_type);
          $modelId   = $this->input('expenseable_id', $expense->expenseable_id);

          $fund = $modelType::find($modelId);

          if ($fund && isset($fund->balance)) {
            $availableBalance = $fund->balance;
            if ($expense->expenseable_type === $modelType && $expense->expenseable_id === $modelId) {
              $availableBalance += $expense->amount;
            }

            if ($value > $availableBalance) {
              $fail("The requested amount ({$value}) exceeds the maximum available balance. Maximum allowed is: {$availableBalance}.");
            }
          }
        },
      ],
      'is_posted'        => ['nullable', 'boolean'],
      'user_id'          => ['sometimes', 'exists:users,id'],
      'created_by'       => ['sometimes', 'exists:users,id'],
    ];
  }
}
