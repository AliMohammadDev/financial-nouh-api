<?php

namespace App\Http\Requests\ReInvoiceItem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateReInvoiceItemRequest extends FormRequest
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
      'reinvoice_id'     => ['required', 'exists:re_invoices,id'],
      'material_id'      => ['required', 'exists:materials,id'],
      'item_description' => ['required', 'string'],
      'unit'             => ['required', 'string', 'max:50'],
      'quantity'         => ['required', 'numeric', 'min:0.01'],
      'unit_price'       => ['required', 'numeric', 'min:0'],
    ];
  }
}
