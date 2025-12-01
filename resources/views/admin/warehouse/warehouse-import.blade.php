@extends('layouts.warehouse')

@section('title', 'Quản lý kho')

@section('content')
@php
/** @var array|null $oldItems */
$oldItems = old('items');
$hasOldItems = is_array($oldItems) && count($oldItems) > 0;
@endphp

<div id="warehouse-import" class="warehouse-section">
  <div class="mb-3">
    <h1 class="display-6 fw-bold text-dark mb-2">Nhập kho</h1>
    <p class="text-muted mb-0">Tạo phiếu nhập kho mới</p>
  </div>

  <div class="warehouse-card card import-card">
    <div class="card-body">
      <form
        id="warehouse-import-form"
        action="{{ route('warehouse.import.handle') }}"
        method="POST">
        @csrf

        {{-- HEADER PHIẾU --}}
        <div class="mb-4 import-header">
          <div class="row align-items-center">
            {{-- LOGO + THÔNG TIN CÔNG TY --}}
            <div class="col-md-4 mb-3 mb-md-0">
              <div class="d-flex align-items-start gap-3">
                <div class="import-logo-box">
                  <span class="import-logo-text">LOGO</span>
                </div>
                <div class="small">
                  <div class="fw-bold text-uppercase">
                    CÔNG TY TNHH MTV LTNQ
                  </div>
                  <div>
                    Mã số thuế: 0318457296012
                  </div>
                  <div>
                    Địa chỉ: 38C, đường Trần Vĩnh Kiết, Quận Ninh Kiều, TP.Cần Thơ
                  </div>
                </div>
              </div>
            </div>

            {{-- TIÊU ĐỀ PHIẾU --}}
            <div class="col-md-4 text-center mb-3 mb-md-0">
              <div class="import-header-title">
                PHIẾU NHẬP KHO
              </div>
            </div>

            {{-- NGÀY LẬP PHIẾU --}}
            <div class="col-md-4 text-md-end">
              <div class="small import-header-date">
                <button
                  type="button"
                  class="btn btn-link p-0 m-0 align-baseline import-date-toggle">
                  <span class="import-date-display">
                    (<span class="text-danger">*</span>) Ngày...
                    Tháng...
                    Năm...
                  </span>
                  <i class="bi bi-calendar3 ms-1"></i>
                </button>

                <input
                  type="date"
                  name="receipt_date"
                  class="import-date-input form-control form-control-sm @error('receipt_date') is-invalid @enderror"
                  value="{{ old('receipt_date') }}">

                @error('receipt_date')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        {{-- THÔNG TIN CHUNG --}}
        <div class="mb-3 row">
          <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
            Họ và tên người giao (<span class="text-danger">*</span>):
          </label>
          <div class="col-md-10">
            <input
              type="text"
              class="form-control form-control-sm dotted-field-line @error('deliver_name') is-invalid @enderror"
              name="deliver_name"
              value="{{ old('deliver_name') }}">
            @error('deliver_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3 row">
          <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
            Đơn vị (<span class="text-danger">*</span>):
          </label>
          <div class="col-md-10">
            <input
              type="text"
              class="form-control form-control-sm dotted-field-line @error('deliver_unit') is-invalid @enderror"
              name="deliver_unit"
              value="{{ old('deliver_unit') }}">
            @error('deliver_unit')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3 row">
          <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
            Địa chỉ (<span class="text-danger">*</span>):
          </label>
          <div class="col-md-10">
            <input
              type="text"
              class="form-control form-control-sm dotted-field-line @error('deliver_address') is-invalid @enderror"
              name="deliver_address"
              value="{{ old('deliver_address') }}">
            @error('deliver_address')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3 row">
          <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
            Theo phiếu giao nhận hàng số (<span class="text-danger">*</span>):
          </label>
          <div class="col-md-10">
            <input
              type="text"
              class="form-control form-control-sm dotted-field-line @error('delivery_number') is-invalid @enderror"
              name="delivery_number"
              value="{{ old('delivery_number') }}">
            @error('delivery_number')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3 row">
          <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
            Nhập nội bộ từ kho (<span class="text-danger">*</span>):
          </label>
          <div class="col-md-10">
            <input
              type="text"
              class="form-control form-control-sm dotted-field-line @error('internal_from_warehouse') is-invalid @enderror"
              name="internal_from_warehouse"
              value="{{ old('internal_from_warehouse') }}">
            @error('internal_from_warehouse')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-4 row align-items-center">
          <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
            Nhà xuất bản (<span class="text-danger">*</span>):
          </label>
          <div class="col-md-4">
            <select
              name="publisher_id"
              class="form-select form-select-sm setupSelect2 @error('publisher_id') is-invalid @enderror"
              id="publisher-select"
              data-products-url="{{ route('warehouse.import.products') }}">
              <option value="">-- Chọn nhà xuất bản --</option>
              @foreach($publishers as $publisher)
              <option
                value="{{ $publisher->id }}"
                @selected(old('publisher_id')==$publisher->id)>
                {{ $publisher->name }}
              </option>
              @endforeach
            </select>
            @error('publisher_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- BẢNG SẢN PHẨM --}}
        <div class="table-responsive mb-3">
          <table class="table table-bordered import-table mb-0">
            <thead class="table-light">
              <tr>
                <th class="col-stt">STT</th>
                <th class="text-start col-product-name">
                  Tên nhãn hiệu, qui cách,<br>
                  phẩm chất hàng hoá, vật tư, dụng cụ
                </th>
                <th class="col-sku">Mã sản phẩm</th>
                <th class="col-unit">Đơn vị tính</th>
                <th class="col-price">Giá nhập/đơn vị sản phẩm</th>
                <th class="col-qty-doc">Số lượng theo chứng từ</th>
                <th class="col-qty-real">Số lượng thực nhập</th>
                <th class="text-start col-note">Ghi chú</th>
                <th class="col-actions">Thêm/Xóa</th>
              </tr>
            </thead>
            <tbody data-has-old-items="{{ $hasOldItems ? '1' : '0' }}">
              @if($hasOldItems)
              @foreach($oldItems as $index => $item)
              <tr>
                <td>{{ $loop->iteration }}</td>

                {{-- Chọn sản phẩm --}}
                <td class="text-start import-product-cell">
                  {{-- Mặc định ẩn thẻ select, chỉ hiện thông báo --}}
                  <span class="import-product-message text-danger">Bạn cần chọn nhà xuất bản</span>

                  <select
                    class="form-select form-select-sm setupSelect2 import-product-select d-none @error(" items.$index.product_id") is-invalid @enderror"
                    name="items[{{ $index }}][product_id]"
                    data-old-value="{{ $item['product_id'] ?? '' }}"
                    {{-- Thêm data-initial-value để JS biết khi nào cần ẩn/hiện --}}>
                    <option value="">Chọn sản phẩm</option>
                    {{-- Options sẽ được JS load theo NXB --}}
                  </select>

                  @error("items.$index.product_id")
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror

                  @if($loop->first)
                  @error('items')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                  @endif
                </td>

                {{-- Mã sản phẩm --}}
                <td>
                  <span class="import-product-code">Mã sản phẩm</span>
                </td>

                {{-- Đơn vị tính --}}
                <td>
                  <span class="import-product-unit">Đơn vị tính</span>
                </td>

                {{-- Giá nhập --}}
                <td>
                  <div class="input-group input-group-sm">
                    <input
                      type="text"
                      class="form-control import-price-display @error(" items.$index.price") is-invalid @enderror"
                      placeholder="0"
                      inputmode="numeric"
                      autocomplete="off"
                      value="{{ old("items.$index.price") }}">
                    <input
                      type="hidden"
                      name="items[{{ $index }}][price]"
                      class="import-price-value"
                      value="{{ old("items.$index.price") }}">
                    <span class="input-group-text">VND</span>
                    <!-- @error("items.$index.price")
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror -->
                  </div>
                </td>

                {{-- Số lượng theo chứng từ --}}
                <td>
                  <input
                    type="number"
                    name="items[{{ $index }}][qty_document]"
                    class="form-control form-control-sm @error(" items.$index.qty_document") is-invalid @enderror"
                    value="{{ old("items.$index.qty_document") }}">
                  <!-- @error("items.$index.qty_document")
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror -->
                </td>

                {{-- Số lượng thực nhập --}}
                <td>
                  <input
                    type="number"
                    name="items[{{ $index }}][qty_real]"
                    class="form-control form-control-sm @error(" items.$index.qty_real") is-invalid @enderror"
                    value="{{ old("items.$index.qty_real") }}">
                  <!-- @error("items.$index.qty_real")
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror -->
                </td>

                {{-- Ghi chú --}}
                <td class="text-start">
                  <input
                    type="text"
                    name="items[{{ $index }}][note]"
                    class="form-control form-control-sm @error(" items.$index.note") is-invalid @enderror"
                    value="{{ old("items.$index.note") }}">
                  @error("items.$index.note")
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </td>

                {{-- Thêm / Xoá --}}
                <td class="import-actions-cell">
                  <div class="import-actions-wrapper">
                    <button
                      type="button"
                      class="btn btn-sm import-btn-icon import-btn-add import-row-add"
                      title="Thêm dòng">
                      <i class="bi bi-plus-lg"></i>
                    </button>

                    @if(!$loop->first)
                    <button
                      type="button"
                      class="btn btn-sm import-btn-icon import-btn-remove import-row-remove"
                      title="Xóa dòng">
                      <i class="bi bi-dash-lg"></i>
                    </button>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
              @else
              {{-- Lần đầu vào form: 1 dòng mẫu --}}
              <tr>
                <td>1</td>
                <td class="text-start import-product-cell">
                  <span class="import-product-message text-danger">
                    Bạn cần chọn nhà xuất bản
                  </span>
                  <select
                    class="form-select form-select-sm setupSelect2 import-product-select d-none"
                    name="items[0][product_id]">
                    <option value="">Chọn sản phẩm</option>
                  </select>
                </td>
                <td>
                  <span class="import-product-code">Mã sản phẩm</span>
                </td>
                <td>
                  <span class="import-product-unit">Đơn vị tính</span>
                </td>
                <td>
                  <div class="input-group input-group-sm">
                    <input
                      type="text"
                      class="form-control import-price-display"
                      placeholder="0"
                      inputmode="numeric"
                      autocomplete="off">
                    <input
                      type="hidden"
                      name="items[0][price]"
                      class="import-price-value">
                    <span class="input-group-text">VND</span>
                  </div>
                </td>
                <td>
                  <input
                    type="number"
                    name="items[0][qty_document]"
                    class="form-control form-control-sm">
                </td>
                <td>
                  <input
                    type="number"
                    name="items[0][qty_real]"
                    class="form-control form-control-sm">
                </td>
                <td class="text-start">
                  <input
                    type="text"
                    name="items[0][note]"
                    class="form-control form-control-sm">
                </td>
                <td class="import-actions-cell">
                  <div class="import-actions-wrapper">
                    <button
                      type="button"
                      class="btn btn-sm import-btn-icon import-btn-add import-row-add"
                      title="Thêm dòng">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>

        {{-- TỔNG CỘNG --}}
        <div class="row justify-content-end mb-4">
          <div class="col-md-4">
            <div class="input-group input-group-sm">
              <span class="input-group-text import-total-label">
                Tổng cộng:
              </span>
              <input
                type="text"
                class="form-control text-end import-total-input"
                value="0 VND"
                readonly>
            </div>
          </div>
        </div>

        {{-- CHỮ KÝ --}}
        <!-- <div class="row text-center import-signature-block mt-5">
          <div class="col-md-4 mb-4 mb-md-0">
            <div class="signature-title">Người giao hàng</div>
            <div class="signature-note">(Ký, ghi rõ họ tên)</div>
          </div>
          <div class="col-md-4 mb-4 mb-md-0">
            <div class="signature-title">Thủ kho</div>
            <div class="signature-note">(Ký, ghi rõ họ tên)</div>
          </div>
          <div class="col-md-4">
            <div class="signature-title">Người lập phiếu</div>
            <div class="signature-note">(Ký, ghi rõ họ tên)</div>
          </div>
        </div> -->
        <div class="mt-4 d-flex justify-content-end">
          <button
            type="submit"
            class="btn btn-warehouse-save">
            <i class="bi bi-save me-2"></i>
            Lưu phiếu
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
@vite(['resources/js/pages/warehouse.js'])
@endpush
@endsection