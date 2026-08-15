<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
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
      'email'    => ['required', 'email', 'exists:users,email'],
      'password' => ['required', 'string', 'min:8', 'confirmed'],
    ];
  }

  public function messages(): array
  {
    return [
      'email.required'     => 'البريد الإلكتروني مطلوب',
      'email.email'        => 'صيغة البريد الإلكتروني غير صحيحة',
      'email.exists'       => 'البريد الإلكتروني غير مسجل ',
      'password.required'  => 'كلمة المرور الجديدة مطلوبة',
      'password.min'       => 'يجب ألا تقل كلمة المرور عن 8 أحرف',
      'password.confirmed' => 'كلمة المرور غير متطابقة مع تأكيد كلمة المرور',
    ];
  }
}
