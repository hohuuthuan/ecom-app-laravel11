<?php

namespace App\Http\Requests\Admin\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ================== HEADER PHIẾU ==================
            'receipt_date' => ['required', 'date'],
            'deliver_name' => ['required', 'string', 'max:191'],
            'deliver_unit' => ['required', 'string', 'max:191'],
            'deliver_address' => ['required', 'string', 'max:255'],
            'delivery_number' => ['required', 'string', 'max:191'],
            'internal_from_warehouse' => ['required', 'string', 'max:191'],

            // publisher_id là UUID
            'publisher_id' => ['required', 'uuid', 'exists:publishers,id'],

            // ================== BẢNG SẢN PHẨM ==================
            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty_document' => ['required', 'integer', 'min:1'],
            'items.*.qty_real' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            // ================== HEADER PHIẾU ==================
            'receipt_date.required' => 'Vui lòng chọn ngày lập phiếu.',
            'receipt_date.date' => 'Ngày lập phiếu không hợp lệ.',

            'deliver_name.required' => 'Vui lòng nhập họ và tên người giao.',
            'deliver_unit.required' => 'Vui lòng nhập đơn vị.',
            'deliver_address.required' => 'Vui lòng nhập địa chỉ.',
            'delivery_number.required' => 'Vui lòng nhập số phiếu giao nhận hàng.',
            'internal_from_warehouse.required' => 'Vui lòng nhập kho nội bộ.',

            'publisher_id.required' => 'Vui lòng chọn nhà xuất bản.',
            'publisher_id.uuid' => 'Định dạng nhà xuất bản không hợp lệ.',
            'publisher_id.exists' => 'Nhà xuất bản không tồn tại trong hệ thống.',

            // ================== ITEMS ==================
            'items.required' => 'Phiếu nhập phải có ít nhất một sản phẩm.',
            'items.array' => 'Dữ liệu sản phẩm không hợp lệ.',
            'items.min' => 'Phiếu nhập phải có ít nhất một sản phẩm.',

            'items.*.product_id.required' => 'Vui lòng chọn sản phẩm.',
            'items.*.product_id.integer' => 'Sản phẩm không hợp lệ.',
            'items.*.product_id.exists' => 'Có sản phẩm không hợp lệ.',

            'items.*.price.required' => 'Thiếu giá nhập',
            'items.*.price.numeric' => 'Giá nhập phải là số.',
            'items.*.price.min' => 'Giá nhập phải >= 0.',

            'items.*.qty_document.required' => 'Thiếu số lượng',
            'items.*.qty_document.integer' => 'Số lượng phải là số nguyên',
            'items.*.qty_document.min' => 'Số lượng phải > 0.',

            'items.*.qty_real.required' => 'Thiếu số lượng',
            'items.*.qty_real.integer' => 'Số lượng phải là số nguyên',
            'items.*.qty_real.min' => 'Số lượng phải > 0.',

            'items.*.note.string' => 'Ghi chú phải là chuỗi ký tự.',
            'items.*.note.max' => 'Ghi chú tối đa :max ký tự.',
        ];
    }
}
