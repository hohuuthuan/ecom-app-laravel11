<?php

namespace App\Http\Requests\User\Address;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Đưa route param {id} vào data để rules có thể validate
   */
  protected function prepareForValidation(): void
  {
    $routeId = $this->route('id');

    if ($routeId) {
      $this->merge([
        'id' => $routeId,
      ]);
    }
  }

  public function rules(): array
  {
    return [
      'id' => ['required', 'uuid'],
    ];
  }

  public function attributes(): array
  {
    return [
      'id' => 'Mã địa chỉ',
    ];
  }

  public function messages(): array
  {
    return [
      'id.required' => 'Vui lòng cung cấp :attribute.',
      'id.uuid'     => ':attribute không hợp lệ.',
    ];
  }
}
