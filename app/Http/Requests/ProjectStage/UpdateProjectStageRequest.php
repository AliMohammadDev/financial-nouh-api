<?php

namespace App\Http\Requests\ProjectStage;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectStageRequest extends FormRequest
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
      'name'              => ['sometimes', 'required', 'string', 'max:255'],
      'start_date'        => ['sometimes', 'required', 'date'],
      'expected_end_date' => ['sometimes', 'required', 'date', 'after_or_equal:start_date'],
      'actual_end_date'   => ['nullable', 'date'],
      'status'            => ['sometimes', 'required', 'string', 'max:50'],
      'stage_progress'    => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
      'project_id'        => ['sometimes', 'required', 'exists:projects,id'],
    ];
  }
}
