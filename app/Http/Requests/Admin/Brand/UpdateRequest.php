<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $id = $this->route('id');
    return [
      'name'        => ['required', 'string', 'max:255'],
      'description' => ['required', 'string'],
      'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
      'slug'        => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('brands', 'slug')->ignore($id, 'id')],
      'status'      => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
    ];
  }

  public function attributes(): array
  {
    return [
      'name' => 'Tên',
      'description' => 'Mô tả',
      'image' => 'Ảnh',
      'slug' => 'Slug',
      'status' => 'Trạng thái'
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Vui lòng nhập :attribute.',
      'name.string'   => ':attribute phải là chuỗi.',
      'name.max'      => ':attribute tối đa 255 ký tự.',

      'description.string' => ':attribute phải là chuỗi.',

      'image.image' => ':attribute phải là ảnh.',
      'image.mimes' => ':attribute chỉ chấp nhận: jpg, jpeg, png, webp.',
      'image.max'   => ':attribute tối đa 2MB.',

      'slug.required'   => 'Vui lòng nhập :attribute.',
      'slug.string'     => ':attribute phải là chuỗi.',
      'slug.max'        => ':attribute tối đa 255 ký tự.',
      'slug.alpha_dash' => ':attribute chỉ gồm chữ, số, gạch ngang và gạch dưới.',
      'slug.unique'     => ':attribute đã tồn tại.',

      'status.required' => 'Vui lòng chọn :attribute.',
      'status.in'       => ':attribute không hợp lệ.',
    ];
  }
}
