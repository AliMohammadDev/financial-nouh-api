<?php

namespace App\Http\Requests\User\Client;

use App\Http\Trait\User\HasUserValidation;
use Illuminate\Foundation\Http\FormRequest;

class CreateClientRequest extends FormRequest
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
    return array_merge($this->userCreateRules(), []);
  }
}