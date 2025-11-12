<?php

namespace App\Http\Requests\User\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
  public function authorize(): bool { return true; }

  public function rules(): array
  {
    return [
      'address'              => ['required','string','max:120'],
      'address_ward_id'      => ['required','integer','min:1'],
      'address_province_id'  => ['required','integer','min:1'],
      'note'                 => ['nullable','string'],
      'default'              => ['sometimes','boolean'],
    ];
  }

  public function attributes(): array
  {
    return [
      'address'             => 'Tên địa chỉ',
      'address_ward_id'     => 'Phường/Xã',
      'address_province_id' => 'Tỉnh/Thành phố',
      'note'                => 'Ghi chú',
      'default'             => 'Đặt làm mặc định',
    ];
  }

  public function messages(): array
  {
    return [
      'address.required' => 'Vui lòng nhập :attribute.',
      'address.string'   => ':attribute phải là chuỗi.',
      'address.max'      => ':attribute tối đa 120 ký tự.',

      'address_ward_id.required' => 'Vui lòng chọn :attribute.',
      'address_ward_id.integer'  => ':attribute phải là số.',
      'address_ward_id.min'      => ':attribute không hợp lệ.',

      'address_province_id.required' => 'Vui lòng chọn :attribute.',
      'address_province_id.integer'  => ':attribute phải là số.',
      'address_province_id.min'      => ':attribute không hợp lệ.',

      'note.string'    => ':attribute phải là chuỗi.',
      'default.boolean'=> ':attribute không hợp lệ.',
    ];
  }
}
