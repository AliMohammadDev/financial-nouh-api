<?php

namespace App\Http\Requests\StageTimeline;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateStageTimelineRequest extends FormRequest
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
      'stage_name'       => ['required', 'string', 'max:255'],
      'start_date'       => ['required', 'date'],
      'expected_end_date' => ['required', 'date', 'after_or_equal:start_date'],
      'actual_end_date'  => ['nullable', 'date'],
      'status'           => ['required', 'string', 'max:50'],
      'stage_progress'   => ['required', 'integer', 'min:0', 'max:100'],
      'project_stage_id' => ['required', 'exists:project_stages,id'],
    ];
  }
}
