<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateAccountRequest extends FormRequest
{
  public function authorize(): bool
  {
    return $this->user()?->hasRole('Admin') ?? false;
  }

  public function rules(): array
  {
    return [
      'status' => ['required', Rule::in(['ACTIVE', 'BAN'])],
      'ids'    => ['required', 'array', 'min:1'],
      'ids.*'  => ['uuid', 'distinct', Rule::exists('users', 'id')],
    ];
  }

  public function messages(): array
  {
    return [
      'ids.required'   => 'Vui lòng chọn ít nhất 1 tài khoản.',
      'ids.min'        => 'Phải chọn ít nhất 1 tài khoản.',
      'ids.*.uuid'     => 'ID tài khoản phải là UUID.',
      'ids.*.distinct' => 'Danh sách UUID có phần tử trùng.',
      'ids.*.exists'   => 'Một số tài khoản không tồn tại.',
      'status.in'      => 'Trạng thái không hợp lệ.',
    ];
  }
}
