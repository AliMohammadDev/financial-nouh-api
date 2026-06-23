<?php

namespace App\Http\Requests\ProjectFund;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateProjectFundRequest extends FormRequest
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
      'project_id'  => 'required|exists:projects,id',
      'name'        => 'required|string|max:255',
      'type'        => 'required|string',
      'currency'    => 'required|string|in:USD,SYP',
      'balance_usd' => 'nullable|numeric|min:0',
      'balance_syp' => 'nullable|numeric|min:0',
    ];
  }
}
