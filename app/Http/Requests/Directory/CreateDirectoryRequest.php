<?php

namespace App\Http\Requests\Directory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateDirectoryRequest extends FormRequest
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
      'dir_name'      => ['required', 'string', 'max:255'],
      'dir_path'      => ['nullable', 'string', 'max:255'],
      'parent_dir_id' => ['nullable', 'exists:directories,id'],
      'project_id'    => ['nullable', 'exists:projects,id'],
      'user_id'    => ['nullable', 'exists:users,id'],
      'is_locked'     => ['nullable', 'boolean'],
    ];
  }
}