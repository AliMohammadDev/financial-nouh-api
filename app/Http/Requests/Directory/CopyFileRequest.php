<?php

namespace App\Http\Requests\Directory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CopyFileRequest extends FormRequest
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
      'media_id'            => ['required', 'integer', 'exists:media,id'],
      'target_directory_id' => ['required', 'integer', 'exists:directories,id'],
    ];
  }
}
