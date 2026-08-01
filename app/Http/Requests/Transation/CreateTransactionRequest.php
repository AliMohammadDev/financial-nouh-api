<?php

namespace App\Http\Requests\Transation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
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
      'morph_from_type' => ['required', 'string'],
      'morph_from_id'   => ['required', 'integer'],
      'morph_to_type'   => ['required', 'string'],
      'morph_to_id'     => ['required', 'integer'],
      'name'            => ['nullable', 'string'],
      'amount'          => ['required', 'numeric', 'min:0.01'],
      'user_id'         => ['nullable', 'exists:users,id'],
    ];
  }
}
