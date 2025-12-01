@extends('layouts.warehouse')

@section('title', 'Chi tiết phiếu nhập kho')

@section('content')
<div id="warehouse-import" class="warehouse-section">
  <div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
      <h1 class="display-6 fw-bold text-dark mb-2">Phiếu Nhập Kho</h1>
      <p class="text-muted mb-0">Xem chi tiết phiếu nhập kho</p>
    </div>
    <div class="text-end small">
      <div><strong>Mã phiếu:</strong> {{ $receipt->receipt_code ?? $receipt->id }}</div>
    </div>
  </div>

  <div class="warehouse-card card import-card">
    <div class="card-body">

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
              @php
                $d = optional($receipt->received_at);
              @endphp
              <span class="import-date-display">
                Ngày {{ $d->format('d') }} tháng {{ $d->format('m') }} năm {{ $d->format('Y') }}
              </span>
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
          <div class="form-control form-control-sm dotted-field-line bg-light-subtle">
            {{ $receipt->name_of_delivery_person }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
          Đơn vị (<span class="text-danger">*</span>):
        </label>
        <div class="col-md-10">
          <div class="form-control form-control-sm dotted-field-line bg-light-subtle">
            {{ $receipt->delivery_unit }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
          Địa chỉ (<span class="text-danger">*</span>):
        </label>
        <div class="col-md-10">
          <div class="form-control form-control-sm dotted-field-line bg-light-subtle">
            {{ $receipt->address_of_delivery_person }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
          Theo phiếu giao nhận hàng số (<span class="text-danger">*</span>):
        </label>
        <div class="col-md-10">
          <div class="form-control form-control-sm dotted-field-line bg-light-subtle">
            {{ $receipt->delivery_note_number }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
          Nhập nội bộ từ kho (<span class="text-danger">*</span>):
        </label>
        <div class="col-md-10">
          <div class="form-control form-control-sm dotted-field-line bg-light-subtle">
            {{ optional($receipt->warehouse)->name }}
          </div>
        </div>
      </div>

      <div class="mb-4 row align-items-center">
        <label class="col-md-2 col-form-label col-form-label-sm warehouse-receive-goods-title">
          Nhà xuất bản (<span class="text-danger">*</span>):
        </label>
        <div class="col-md-4">
          <div class="form-control form-control-sm bg-light-subtle">
            {{ optional($receipt->publisher)->name }}
          </div>
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
          <tbody>
            @forelse($receipt->items as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="text-start import-product-cell">
                  <span class="import-product-name">
                    {{ optional($item->product)->title }}
                  </span>
                </td>
                <td>
                  <span class="import-product-code">
                    {{ optional($item->product)->code }}
                  </span>
                </td>
                <td>
                  <span class="import-product-unit">
                    {{ optional($item->product)->unit }}
                  </span>
                </td>
                <td>
                  <div class="input-group input-group-sm">
                    <span class="form-control import-price-display text-end bg-light-subtle">
                      {{ number_format($item->import_price_vnd) }}
                    </span>
                    <span class="input-group-text">VND</span>
                  </div>
                </td>
                <td>
                  <div class="form-control form-control-sm text-end bg-light-subtle">
                    {{ $item->qty_doc }}
                  </div>
                </td>
                <td>
                  <div class="form-control form-control-sm text-end bg-light-subtle">
                    {{ $item->qty_actual }}
                  </div>
                </td>
                <td class="text-start">
                  <div class="form-control form-control-sm bg-light-subtle">
                    {{ $item->notes }}
                  </div>
                </td> 
                <td class="import-actions-cell">
                  <div class="import-actions-wrapper text-center text-muted small">
                    &mdash;
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-3 text-muted">
                  Phiếu này chưa có dòng hàng hóa nào.
                </td>
              </tr>
            @endforelse
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
            <span class="form-control text-end import-total-input bg-light-subtle">
              {{ number_format($receipt->sub_total_vnd) }} VND
            </span>
          </div>
        </div>
      </div>

      <!-- <div class="row text-center import-signature-block mt-5">
        <div class="col-md-4 mb-4 mb-md-0">
          <div class="signature-title">Người giao hàng</div>
          <div class="signature-note">(Ký, ghi rõ họ tên)</div>
          <div class="mt-4 fw-semibold">
            {{ $receipt->name_of_delivery_person }}
          </div>
        </div>
        <div class="col-md-4 mb-4 mb-md-0">
          <div class="signature-title">Thủ kho</div>
          <div class="signature-note">(Ký, ghi rõ họ tên)</div>
        </div>
        <div class="col-md-4">
          <div class="signature-title">Người lập phiếu</div>
          <div class="signature-note">(Ký, ghi rõ họ tên)</div>
          <div class="mt-4 fw-semibold">
            {{ optional($receipt->createdBy)->name }}
          </div>
        </div>
      </div> -->

      <!-- <div class="mt-4 d-flex justify-content-between">
        <a
          href="{{ route('warehouse.purchase_receipts.index') }}"
          class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-2"></i>
          Quay lại danh sách
        </a>
      </div> -->
    </div>
  </div>
</div>
@endsection
