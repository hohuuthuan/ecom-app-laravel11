<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'email'    => 'bail|required|string|email:rfc,dns|max:255',
      'password' => 'bail|required|string|min:6',
      'remember' => 'sometimes|boolean'
    ];
  } 

  public function attributes(): array
  {
    return [
      'email'    => 'Email',
      'password' => 'Mật khẩu',
      'remember' => 'Ghi nhớ đăng nhập',
    ];
  }

  public function messages(): array
  {
    return [
      'required' => ':attribute không được để trống.',
      'string'   => ':attribute phải là chuỗi ký tự.',
      'email'    => ':attribute không đúng định dạng.',
      'max'      => ':attribute không được vượt quá :max ký tự.',
      'min'      => ':attribute phải có ít nhất :min ký tự.',
      'boolean'  => ':attribute không hợp lệ.',
    ];
  }
}
