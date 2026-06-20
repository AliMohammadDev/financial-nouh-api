<?php

namespace App\Http\Requests\Material;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
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
      'item_id' => 'sometimes|required|exists:items,id',
      'name'               => 'sometimes|required|string|max:255',
      'unit'               => 'sometimes|required|string|max:50',
      'description'        => 'nullable|string',
    ];
  }
}
