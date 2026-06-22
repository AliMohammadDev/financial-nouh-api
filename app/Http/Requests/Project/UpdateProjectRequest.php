<?php

namespace App\Http\Requests\Project;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
      'client_id'     => 'sometimes|required|exists:clients,id',
      'name'          => 'sometimes|required|string|max:255',
      'expected_cost' => 'sometimes|required|numeric|min:0',
      'status'        => 'sometimes|required|string|in:pending,in_progress,completed,cancelled',
    ];
  }
}
