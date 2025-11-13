<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Đưa lỗi vào error bag "profile" để tách biệt với form khác.
     */
    protected $errorBag = 'profile';

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $user = Auth::user();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'birthday' => [
                'nullable',
                'date',
                'before:today',
            ],
            'gender' => [
                'nullable',
                'in:male,female,other',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Họ và tên',
            'phone' => 'Số điện thoại',
            'email' => 'Email',
            'birthday' => 'Ngày sinh',
            'gender' => 'Giới tính',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'birthday.date' => 'Ngày sinh không hợp lệ.',
            'birthday.before' => 'Ngày sinh phải nhỏ hơn ngày hiện tại.',
            'gender.in' => 'Giới tính không hợp lệ.',
        ];
    }
}
