<?php

namespace App\Http\Requests\Material;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateMaterialRequest extends FormRequest
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
      'item_id' => 'required|exists:items,id',
      'name'               => 'required|string|max:255',
      'unit'               => 'required|string|max:50',
      'description'        => 'nullable|string',
    ];
  }
}
