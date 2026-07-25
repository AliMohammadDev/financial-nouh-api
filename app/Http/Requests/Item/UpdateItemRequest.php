<?php

namespace App\Http\Requests\Item;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
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
    $itemId = $this->route('item');
    $id = is_object($itemId) ? $itemId->id : $itemId;

    return [
      'name'        => 'sometimes|required|string|max:255|unique:items,name,' . $id,
      'description' => 'nullable|string',
    ];
  }
}