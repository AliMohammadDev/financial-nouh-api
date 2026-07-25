<?php

namespace App\Http\Requests\ProjectTeam;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectTeamRequest extends FormRequest
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
      'name'       => ['sometimes', 'required', 'string', 'max:255'],
      'user_id'    => ['sometimes', 'required', 'exists:users,id'],
      'project_id' => ['sometimes', 'required', 'exists:projects,id'],
    ];
  }
}
