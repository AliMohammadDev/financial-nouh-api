<?php

namespace App\Http\Requests\Department;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
    $departmentId = $this->route('department');
    $id = is_object($departmentId) ? $departmentId->id : $departmentId;

    return [
      'name'         => ['sometimes', 'required', 'string', 'max:255', 'unique:departments,name,' . $id . ',id'],
      'main_manager' => ['nullable', 'string', 'max:255'],
    ];
  }
}
