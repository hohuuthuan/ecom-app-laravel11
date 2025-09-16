<?php

namespace App\Http\Requests\Admin\Account;

use Illuminate\Foundation\Http\FormRequest;

class BulkStatusRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  protected function prepareForValidation(): void
  {
    $status = strtoupper((string) $this->input('status'));
    if ($status === 'BAN') {
      $this->merge(['status' => 'INACTIVE']);
    }
  }

  public function rules(): array
  {
    return [
      'status' => ['required', 'string', 'in:ACTIVE,INACTIVE'],
      'ids' => ['required', 'array', 'min:1'],
      'ids.*' => ['uuid'],
    ];
  }

  public function attributes(): array
  {
    return [
      'status' => 'trạng thái',
      'ids' => 'danh sách tài khoản',
    ];
  }

  public function messages(): array
  {
    return [
      'status.required' => 'Vui lòng chọn :attribute.',
      'status.in' => ':attribute không hợp lệ.',
      'ids.required' => 'Vui lòng chọn ít nhất một tài khoản.',
      'ids.array' => ':attribute không hợp lệ.',
      'ids.min' => 'Cần chọn ít nhất :min tài khoản.',
      'ids.*.uuid' => 'ID tài khoản không hợp lệ.',
    ];
  }
}
