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
      'expenseable_type' => ['required', 'string', 'in:App\Models\CurrencyFund,App\Models\CompanyFundCurrency,App\Models\ProjectFundCurrency'],
      'expenseable_id'   => ['required', 'integer'],
      'description'      => ['required', 'string'],
      'amount'           => [
        'required',
        'numeric',
        'min:0.01',
        // function ($attribute, $value, $fail) {
        //   $modelType = $this->input('expenseable_type');
        //   $modelId   = $this->input('expenseable_id');

        //   if ($modelType && $modelId) {
        //     $fund = $modelType::find($modelId);

        //     if ($fund && isset($fund->balance)) {
        //       if ($value > $fund->balance) {
        //         $fail("The requested amount ({$value}) exceeds the available balance. Current balance is: {$fund->balance}.");
        //       }
        //     } else {
        //       $fail('The selected fund does not exist.');
        //     }
        //   }
        // },
      ],
      'note' => ['nullable', 'string'],
      'is_posted'        => ['nullable', 'boolean'],
      'created_by'       => ['nullable', 'exists:users,id'],
    ];
  }
}
