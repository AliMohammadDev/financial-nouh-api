<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
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
      'item_id'              => ['required', 'exists:items,id'],
      'expense_id'         => ['nullable', 'exists:expenses,id'],
      'supplier_id'          => ['nullable', 'exists:users,id'],
      // 'invoice_number'       => ['required', 'string', 'unique:invoices,invoice_number'],
      'date'                 => ['nullable', 'date'],
      'discount'             => ['nullable', 'numeric', 'min:0'],
      'final_total'          => ['required', 'numeric', 'min:0'],
      'is_posted'            => ['nullable', 'boolean'],
      'is_visible_to_client' => ['nullable', 'boolean'],
    ];
  }
}
