<?php

namespace App\Http\Requests\Increment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIncrementRequest extends FormRequest
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
      'employee_id' => ['sometimes', 'required', 'exists:employees,id'],
      'type'        => ['sometimes', 'required', 'string', 'max:255'],
      'amount'      => ['sometimes', 'required', 'numeric', 'min:0'],
      'reason'      => ['nullable', 'string'],
    ];
  }
}
