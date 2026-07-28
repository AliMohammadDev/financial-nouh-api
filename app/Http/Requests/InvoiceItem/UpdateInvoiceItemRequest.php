<?php

namespace App\Http\Requests\InvoiceItem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceItemRequest extends FormRequest
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
      'invoice_id'       => ['sometimes', 'exists:invoices,id'],
      'material_id'      => ['sometimes', 'exists:materials,id'],
      'item_description' => ['sometimes', 'string'],
      'unit'             => ['sometimes', 'string', 'max:50'],
      'quantity'         => ['sometimes', 'numeric', 'min:0.01'],
      'unit_price'       => ['sometimes', 'numeric', 'min:0'],
    ];
  }
}
