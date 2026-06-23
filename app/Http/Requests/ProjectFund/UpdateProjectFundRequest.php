<?php

namespace App\Http\Requests\ProjectFund;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectFundRequest extends FormRequest
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
      'project_id'  => 'sometimes|exists:projects,id',
      'name'        => 'sometimes|string|max:255',
      'type'        => 'sometimes|string',
      'currency'    => 'sometimes|string|in:USD,SYP',
      'balance_usd' => 'nullable|numeric|min:0',
      'balance_syp' => 'nullable|numeric|min:0',
    ];
  }
}
