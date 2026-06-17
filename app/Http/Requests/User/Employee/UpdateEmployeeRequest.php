<?php

namespace App\Http\Requests\User\Employee;

use App\Http\Trait\User\HasUserValidation;
use App\Models\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
  use HasUserValidation;

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
    $employee = Employee::find($this->route('employee'));
    return array_merge($this->userUpdateRules($employee?->user_id), [
      'job_title' => ['sometimes', 'required', 'string', 'max:255'],
    ]);
  }
}
