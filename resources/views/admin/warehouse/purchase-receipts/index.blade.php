@extends('layouts.warehouse')

@section('title','Danh sách phiếu nhập')

@section('content')
<div id="warehouse-receipts" class="warehouse-section">

  <div class="d-flex justify-content-between align-items-center mb-5">
    <div>
      <h1 class="display-6 fw-bold text-dark mb-2">Danh Sách Phiếu Nhập Kho</h1>
      <p class="text-muted">Xem, lọc và truy cập chi tiết các phiếu nhập</p>
    </div>
    <a href="{{ route('warehouse.import') }}" class="btn btn-primary btn-new-purchase-receipt">
      + Tạo phiếu nhập mới
    </a>
  </div>

  {{-- FILTER FROM - TO --}}
  <div class="warehouse-card card mb-4">
    <div class="card-body">
      <form method="GET"
        action="{{ route('warehouse.purchase_receipts.index') }}"
        class="row g-3">

        <div class="col-md-2">
          <label class="form-label fw-medium">Từ ngày</label>
          <input type="date"
            name="date_from"
            class="form-control warehouse-form-control"
            value="{{ request('date_from') }}">
        </div>

        <div class="col-md-2">
          <label class="form-label fw-medium">Đến ngày</label>
          <input type="date"
            name="date_to"
            class="form-control warehouse-form-control"
            value="{{ request('date_to') }}">
        </div>

        <div class="col-md-2">
          <label class="form-label fw-medium">Nhà xuất bản</label>
          <select
            name="publisher_id"
            class="form-select warehouse-form-control setupSelect2">
            <option value="">-- Tất cả nhà xuất bản --</option>
            @foreach($publishers as $publisher)
            <option
              value="{{ $publisher->id }}"
              @selected(request('publisher_id')==$publisher->id)>
              {{ $publisher->name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-medium d-block">&nbsp;</label>
          <button class="btn btn-dark px-4">Lọc</button>
          <a href="{{ route('warehouse.purchase_receipts.index') }}"
            class="btn btn-outline-secondary ms-1">
            Xóa lọc
          </a>
        </div>
      </form>

    </div>
  </div>

  <div class="warehouse-card card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 warehouse-table">
          <thead>
            <tr>
              <th class="px-4 py-3">Mã Phiếu</th>
              <th class="px-4 py-3">Nhà Xuất Bản</th>
              <th class="px-4 py-3">Ngày Nhập</th>
              <th class="px-4 py-3">Người Giao</th>
              <th class="px-4 py-3">Tổng Tiền</th>
              <th class="px-4 py-3">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse($receipts as $r)
            <tr>
              <td class="px-4 py-3 fw-medium">{{ $r->receipt_code }}</td>
              <td class="px-4 py-3">{{ optional($r->publisher)->name }}</td>
              <td class="px-4 py-3">
                {{ $r->created_at ? $r->created_at->format('d/m/Y, A') : '' }}
              </td>
              <td class="px-4 py-3 text-muted">
                {{ $r->name_of_delivery_person }}
              </td>
              <td class="px-4 py-3">
                {{ number_format($r->sub_total_vnd) }} VNĐ
              </td>
              <td class="px-4 py-3">
                <a href="{{ route('warehouse.purchase_receipts.show', $r->id) }}"
                  class="btn btn-sm">
                  <i class="fa fa-eye icon-eye-view-order-detail"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">
                Chưa có phiếu nhập nào.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $receipts->links() }}
      </div>
    </div>
  </div>
</div>
@endsection