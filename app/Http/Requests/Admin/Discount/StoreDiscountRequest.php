<?php

namespace App\Http\Requests\Admin\Discount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountRequest extends FormRequest
{
  protected $errorBag = 'discountCreate';

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'code' => [
        'required',
        'string',
        'max:64',
        'regex:/^[A-Z0-9\-_]+$/',
        Rule::unique('discounts', 'code'),
      ],
      'type' => [
        'required',
        Rule::in(['percent', 'fixed', 'shipping']),
      ],
      'value' => [
        'required',
        'integer',
        'min:1',
        Rule::when(
          $this->input('type') === 'percent',
          ['max:100']
        ),
      ],
      'min_order_value_vnd' => [
        'nullable',
        'integer',
        'min:0',
      ],
      'usage_limit' => [
        'nullable',
        'integer',
        'min:1',
      ],
      'per_user_limit' => [
        'nullable',
        'integer',
        'min:1',
      ],
      'start_date' => [
        'nullable',
        'date',
      ],
      'end_date' => [
        'nullable',
        'date',
        'after_or_equal:start_date',
      ],
      'status' => [
        'required',
        Rule::in(['ACTIVE', 'INACTIVE']),
      ],
    ];
  }

  public function attributes(): array
  {
    return [
      'code' => 'Mã giảm giá',
      'type' => 'Loại mã giảm giá',
      'value' => 'Giá trị',
      'min_order_value_vnd' => 'Đơn tối thiểu',
      'usage_limit' => 'Giới hạn lượt dùng (tổng)',
      'per_user_limit' => 'Giới hạn mỗi người dùng',
      'start_date' => 'Ngày bắt đầu',
      'end_date' => 'Ngày kết thúc',
      'status' => 'Trạng thái',
    ];
  }

  public function messages(): array
  {
    return [
      'code.required' => 'Vui lòng nhập Mã giảm giá.',
      'code.string' => 'Mã giảm giá phải là chuỗi.',
      'code.max' => 'Mã giảm giá tối đa 64 ký tự.',
      'code.regex' => 'Mã giảm giá chỉ gồm chữ in hoa, số, dấu gạch ngang và gạch dưới.',
      'code.unique' => 'Mã giảm giá đã tồn tại.',

      'type.required' => 'Vui lòng chọn Loại mã giảm giá.',
      'type.in' => 'Loại mã giảm giá không hợp lệ.',

      'value.required' => 'Vui lòng nhập Giá trị giảm.',
      'value.integer' => 'Giá trị giảm phải là số nguyên.',
      'value.min' => 'Giá trị giảm phải lớn hơn 0.',
      'value.max' => 'Nếu là giảm theo %, Giá trị giảm phải từ 1 đến 100.',

      'min_order_value_vnd.integer' => 'Đơn tối thiểu phải là số.',
      'min_order_value_vnd.min' => 'Đơn tối thiểu không được âm.',

      'usage_limit.integer' => 'Giới hạn lượt dùng phải là số.',
      'usage_limit.min' => 'Giới hạn lượt dùng phải lớn hơn 0.',

      'per_user_limit.integer' => 'Giới hạn mỗi người dùng phải là số.',
      'per_user_limit.min' => 'Giới hạn mỗi người dùng phải lớn hơn 0.',

      'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
      'end_date.date' => 'Ngày kết thúc không hợp lệ.',
      'end_date.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng Ngày bắt đầu.',

      'status.required' => 'Vui lòng chọn Trạng thái.',
      'status.in' => 'Trạng thái không hợp lệ.',
    ];
  }
}
