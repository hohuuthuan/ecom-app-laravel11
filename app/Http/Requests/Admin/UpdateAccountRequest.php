<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $id = $this->route('id');

    return [
      'name'      => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{N}\s]+$/u', 'not_regex:/^\d+$/'],
      'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id, 'id')],
      'phone'     => ['nullable', 'digits:10', 'starts_with:0'],
      'status'    => ['required', Rule::in(['ACTIVE', 'BAN'])],
      'avatar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

      'role_ids'   => ['required', 'array', 'min:1'],
      'role_ids.*' => ['uuid', 'exists:roles,id', 'distinct'],
    ];
  }

  public function attributes(): array
  {
    return [
      'name'      => 'Họ tên',
      'email'     => 'Email',
      'phone'     => 'Số điện thoại',
      'status'    => 'Trạng thái',
      'avatar'    => 'Ảnh đại diện',
      'role_ids'  => 'Phân quyền',
    ];
  }

  public function messages(): array
  {
    return [
      'name.required'   => 'Vui lòng nhập Họ tên.',
      'name.string'     => 'Họ tên phải là chuỗi.',
      'name.max'        => 'Họ tên tối đa 255 ký tự.',
      'name.regex'      => 'Họ tên chỉ gồm chữ, số và khoảng trắng.',
      'name.not_regex'  => 'Họ tên không thể chỉ toàn số.',

      'email.required' => 'Vui lòng nhập Email.',
      'email.string'   => 'Email phải là chuỗi.',
      'email.email'    => 'Email không hợp lệ.',
      'email.max'      => 'Email tối đa 255 ký tự.',
      'email.unique'   => 'Email đã được sử dụng.',

      'phone.digits'       => 'Số điện thoại phải gồm 10 chữ số.',
      'phone.starts_with'  => 'Số điện thoại phải bắt đầu bằng 0.',

      'status.required' => 'Vui lòng chọn Trạng thái.',
      'status.in'       => 'Trạng thái không hợp lệ.',

      'avatar.image' => 'Ảnh đại diện phải là ảnh.',
      'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận: jpg, jpeg, png, webp.',
      'avatar.max'   => 'Ảnh đại diện tối đa 2MB.',

      'role_ids.required'   => 'Vui lòng chọn ít nhất 1 vai trò.',
      'role_ids.array'      => 'Phân quyền không hợp lệ.',
      'role_ids.min'        => 'Cần ít nhất 1 vai trò.',
      'role_ids.*.uuid'     => 'Mỗi vai trò phải là UUID hợp lệ.',
      'role_ids.*.exists'   => 'Vai trò được chọn không tồn tại.',
      'role_ids.*.distinct' => 'Vai trò bị trùng lặp.',
    ];
  }
}
