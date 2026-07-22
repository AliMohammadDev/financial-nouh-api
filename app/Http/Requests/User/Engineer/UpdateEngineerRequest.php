<?php

namespace App\Http\Requests\User\Engineer;

use App\Http\Trait\User\HasUserValidation;
use App\Models\Engineer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEngineerRequest extends FormRequest
{
  use HasUserValidation;

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
    $engineer = $this->route('engineer');
    $userId = $engineer?->user_id;

    return array_merge($this->userUpdateRules($userId), [
      'job_title'   => ['sometimes', 'required', 'string', 'max:255'],
      'base_salary' => ['sometimes', 'required', 'numeric', 'min:0'],
    ]);
  }
}
