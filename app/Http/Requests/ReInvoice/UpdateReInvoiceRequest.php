<?php

namespace App\Http\Requests\ReInvoice;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReInvoiceRequest extends FormRequest
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
      'item_id'              => ['sometimes', 'exists:items,id'],
      'supplier_id'          => ['nullable', 'exists:users,id'],
      'reinvoiceable_type'   => ['sometimes', 'string'],
      'reinvoiceable_id'     => ['sometimes', 'integer'],
      'date'                 => ['nullable', 'date'],
      'discount'             => ['nullable', 'numeric', 'min:0'],
      'final_total'          => ['sometimes', 'numeric', 'min:0'],
      'is_posted'            => ['nullable', 'boolean'],
      'is_visible_to_client' => ['nullable', 'boolean'],
    ];
  }
}
