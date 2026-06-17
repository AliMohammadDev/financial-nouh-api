<?php

namespace App\Http\Requests\User\Client;

use App\Http\Trait\User\HasUserValidation;
use App\Models\Client;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
  use HasUserValidation;

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
    $client = Client::find($this->route('client'));

    return array_merge($this->userUpdateRules($client?->user_id), []);
  }
}