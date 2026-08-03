<?php

namespace App\Http\Requests\Transation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
      'morph_from_type' => ['sometimes', 'string'],
      'morph_from_id'   => ['sometimes', 'integer'],
      'morph_to_type'   => ['sometimes', 'string'],
      'morph_to_id'     => ['sometimes', 'integer'],
      'name'            => ['nullable', 'string'],
      'amount'          => ['sometimes', 'numeric', 'min:0.01'],
      'created_by'      => ['sometimes', 'exists:users,id'],
    ];
  }
}
