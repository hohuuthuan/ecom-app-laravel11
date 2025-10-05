<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'title'             => ['required', 'string', 'max:255'],
      'slug'              => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
      'code'              => ['required', 'string', 'max:64', 'alpha_dash'],
      'isbn'              => ['required', 'string', 'max:32'],
      'description'       => ['required', 'string'],
      'selling_price_vnd' => ['required', 'integer', 'min:1'],
      'unit'              => ['required', 'string', 'max:50'],
      'status'            => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
      'publisher_id'      => ['required', 'uuid'],
      'categoriesInput'   => ['required', 'array', 'min:1'],
      'categoriesInput.*' => ['required', 'uuid'],
      'authorsInput'      => ['required', 'array', 'min:1'],
      'authorsInput.*'    => ['required', 'uuid'],

      'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
    ];
  }

  public function attributes(): array
  {
    return [
      'title' => 'tiêu đề',
      'slug' => 'slug',
      'code' => 'mã sản phẩm',
      'isbn' => 'ISBN',
      'description' => 'mô tả',
      'selling_price_vnd' => 'giá bán (VND)',
      'unit' => 'đơn vị',
      'status' => 'trạng thái',
      'publisher_id' => 'nhà xuất bản',
      'categoriesInput' => 'danh mục',
      'categoriesInput.*' => 'danh mục',
      'authorsInput' => 'tác giả',
      'authorsInput.*' => 'tác giả',
      'image' => 'hình ảnh sản phẩm',
    ];
  }

  public function messages(): array
  {
    return [
      'title.required' => 'Vui lòng nhập :attribute',
      'title.string'   => ':attribute phải là chuỗi',
      'title.max'      => ':attribute tối đa 255 ký tự',

      'slug.required' => 'Vui lòng nhập :attribute',
      'slug.string'   => ':attribute phải là chuỗi',
      'slug.max'      => ':attribute tối đa 255 ký tự',
      'slug.regex'    => ':attribute chỉ gồm chữ thường, số và gạch ngang',

      'code.required'   => 'Vui lòng nhập :attribute',
      'code.string'     => ':attribute phải là chuỗi',
      'code.max'        => ':attribute tối đa 64 ký tự',
      'code.alpha_dash' => ':attribute chỉ gồm chữ, số, gạch ngang và gạch dưới',

      'isbn.required' => 'Vui lòng nhập :attribute',
      'isbn.string'   => ':attribute phải là chuỗi',
      'isbn.max'      => ':attribute tối đa 32 ký tự',

      'description.required' => 'Vui lòng nhập :attribute',
      'description.string'   => ':attribute phải là chuỗi',

      'selling_price_vnd.required' => 'Vui lòng nhập :attribute',
      'selling_price_vnd.integer'  => ':attribute phải là số nguyên',
      'selling_price_vnd.min'      => ':attribute phải lớn hơn 0',

      'unit.required' => 'Vui lòng nhập :attribute',
      'unit.string'   => ':attribute phải là chuỗi',
      'unit.max'      => ':attribute tối đa 50 ký tự',

      'status.required' => 'Vui lòng chọn :attribute',
      'status.in'       => ':attribute không hợp lệ',

      'publisher_id.required' => 'Vui lòng chọn :attribute',
      'publisher_id.uuid'     => ':attribute không hợp lệ',

      'categoriesInput.required'   => 'Vui lòng chọn :attribute',
      'categoriesInput.array'      => ':attribute không hợp lệ',
      'categoriesInput.min'        => 'Cần chọn ít nhất 1 :attribute',
      'categoriesInput.*.uuid'     => ':attribute không hợp lệ',

      'authorsInput.required'   => 'Vui lòng chọn :attribute',
      'authorsInput.array'      => ':attribute không hợp lệ',
      'authorsInput.min'        => 'Cần chọn ít nhất 1 :attribute',
      'authorsInput.*.uuid'     => ':attribute không hợp lệ',

      'image.image' => ':attribute phải là ảnh',
      'image.mimes' => ':attribute chỉ chấp nhận: jpg, jpeg, png, webp',
      'image.max'   => ':attribute tối đa 10MB',
    ];
  }


  protected function prepareForValidation(): void
  {
    if ($this->filled('slug')) {
      $this->merge(['slug' => Str::slug($this->input('slug'))]);
    }
  }
}
