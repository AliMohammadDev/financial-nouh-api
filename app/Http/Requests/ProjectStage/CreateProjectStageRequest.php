<?php

namespace App\Http\Requests\ProjectStage;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProjectStageRequest extends FormRequest
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
      'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('project_stages', 'name')->where(function ($query) {
          return $query->where('project_id', $this->project_id);
        }),
      ],
      'start_date'        => ['required', 'date'],
      'expected_end_date' => ['required', 'date', 'after_or_equal:start_date'],
      'actual_end_date'   => ['nullable', 'date'],
      'status'            => ['required', 'string', 'max:50'],
      'stage_progress'    => ['required', 'integer', 'min:0', 'max:100'],
      'project_id'        => ['required', 'exists:projects,id'],
    ];
  }
}
