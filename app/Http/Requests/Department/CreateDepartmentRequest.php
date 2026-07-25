<?php

namespace App\Http\Requests\Department;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateDepartmentRequest extends FormRequest
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
      'Name'         => ['required', 'string', 'max:255', 'unique:departments,Name'],
      'Main_Manager' => ['nullable', 'string', 'max:255'],
    ];
  }
}
