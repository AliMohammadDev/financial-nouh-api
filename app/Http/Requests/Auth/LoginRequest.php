<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
      'email' => ['required', 'email'],
      'password' => ['required'],
    ];
  }

  public function messages(): array
  {
    return [
      'email.required'    => 'البريد الإلكتروني مطلوب',
      'email.email'       => 'صيغة البريد الإلكتروني غير صحيحة',
      'password.required' => 'كلمة المرور مطلوبة',
    ];
  }
}
