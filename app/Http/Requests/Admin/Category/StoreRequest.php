<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoreRequest extends FormRequest
{
  public function authorize(): bool { return true; }

  public function rules(): array
  {
    return [
      'name'        => ['required','string','max:255'],
      'description' => ['required','string'],
      'slug'        => ['required','string','max:255','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories','slug')],
      'status'      => ['required', Rule::in(['ACTIVE','INACTIVE'])],
    ];
  }

  public function attributes(): array
  {
    return [
      'name'        => 'Tên',
      'description' => 'Mô tả',
      'slug'        => 'Slug',
      'status'      => 'Trạng thái',
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Vui lòng nhập :attribute.',
      'name.string'   => ':attribute phải là chuỗi.',
      'name.max'      => ':attribute tối đa 255 ký tự.',

      'description.required' => 'Vui lòng nhập :attribute.',
      'description.string'   => ':attribute phải là chuỗi.',

      'slug.required' => 'Vui lòng nhập :attribute.',
      'slug.string'   => ':attribute phải là chuỗi.',
      'slug.max'      => ':attribute tối đa 255 ký tự.',
      'slug.regex'    => ':attribute chỉ gồm chữ thường, số và gạch ngang.',
      'slug.unique'   => ':attribute đã tồn tại.',

      'status.required' => 'Vui lòng chọn :attribute.',
      'status.in'       => ':attribute không hợp lệ.',
    ];
  }

  protected function prepareForValidation(): void
  {
    if ($this->filled('slug')) {
      $this->merge(['slug' => Str::slug($this->input('slug'))]);
    }
  }
}
