<?php

namespace App\Http\Requests\EmployeePayment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeePaymentRequest extends FormRequest
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
      'employee_id'  => 'required|exists:employees,id',
      'bonuses'      => 'nullable|numeric|min:0',
      'deductions'   => 'nullable|numeric|min:0',
      'payment_date' => 'required|date',
      'amount'       => 'required|numeric|min:0',
    ];
  }
}
