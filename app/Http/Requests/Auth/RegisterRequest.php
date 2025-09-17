<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name'      => 'required|string|max:255|regex:/^[\p{L}\p{N}\s]+$/u|not_regex:/^\d+$/',
      'email'     => 'required|string|email|max:255',
      'password'  => 'required|string|min:6|confirmed',
      'phone'     => 'required|digits:10|starts_with:0',
    ];
  }

  public function attributes(): array
  {
    return [
      'name'                  => 'Họ tên',
      'phone'                 => 'Số điện thoại',
      'email'                 => 'Email',
      'password'              => 'Mật khẩu',
      'password_confirmation' => 'Xác nhận mật khẩu',
    ];
  }

  public function messages(): array
  {
    return [
      'required'               => ':attribute không được để trống.',
      'string'                 => ':attribute phải là chuỗi ký tự.',
      'email'                  => ':attribute không đúng định dạng.',
      'min'                    => ':attribute phải có ít nhất :min ký tự.',
      'password.confirmed'     => 'Mật khẩu và xác nhận mật khẩu không khớp.',
      'name.regex'             => 'Họ tên chỉ được chứa chữ cái, số và khoảng trắng.',
      'name.not_regex'         => 'Họ tên không được toàn là số.',
      'phone.digits'           => 'Số điện thoại phải gồm đúng 10 chữ số.',
      'phone.starts_with'      => 'Số điện thoại phải bắt đầu bằng số 0.',
    ];
  }
}
