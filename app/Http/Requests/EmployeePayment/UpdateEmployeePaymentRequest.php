<?php

namespace App\Http\Requests\EmployeePayment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeePaymentRequest extends FormRequest
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
      'employee_id'  => 'sometimes|required|exists:employees,id',
      'bonuses'      => 'sometimes|nullable|numeric|min:0',
      'deductions'   => 'sometimes|nullable|numeric|min:0',
      'payment_date' => 'sometimes|required|date',
      'amount'       => 'sometimes|required|numeric|min:0',
    ];
  }
}
