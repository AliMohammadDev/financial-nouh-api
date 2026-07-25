<?php

namespace App\Http\Requests\ProjectTeam;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateProjectTeamRequest extends FormRequest
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
      'name'       => ['required', 'string', 'max:255'],
      'user_id'    => ['required', 'exists:users,id'],
      'project_id' => ['required', 'exists:projects,id'],
    ];
  }
}
