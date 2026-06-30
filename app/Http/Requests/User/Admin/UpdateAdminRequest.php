<?php

namespace App\Http\Requests\User\Admin;

use App\Http\Trait\User\HasUserValidation;
use App\Models\Admin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
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
    $admin = Admin::find($this->route('admin'));
    return array_merge($this->userUpdateRules($admin?->user_id), []);
  }
}
