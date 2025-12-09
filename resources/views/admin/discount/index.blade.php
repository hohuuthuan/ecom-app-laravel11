@extends('layouts.admin')

@section('title','Discounts: Mã giảm giá')

@section('body_class','discount-index-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Mã giảm giá</li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách mã giảm giá</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        <?php $pp = (int) request('per_page_discount', 20); ?>
        <select
          class="form-select form-select-sm w-auto setupSelect2"
          name="per_page_discount"
          onchange="this.form.submit()">
          <option value="10" {{ $pp === 10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ $pp === 20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ $pp === 50 ? 'selected' : '' }}>50</option>
        </select>

        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
        <input type="hidden" name="type" value="{{ request('type') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="start_from" value="{{ request('start_from') }}">
        <input type="hidden" name="start_to" value="{{ request('start_to') }}">
      </form>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form method="GET" class="row g-2 mb-3 filter-form">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-6 searchProduct">
              <label for="keyword" class="form-label mb-1 label-filter-admin-product">Tìm kiếm</label>
              <input
                id="keyword"
                type="text"
                name="keyword"
                class="form-control"
                placeholder="Tìm theo mã giảm giá"
                value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
              <label class="d-block mb-1">&nbsp;</label>
              <button type="submit" class="btn-admin">
                <i class="fa fa-search me-1"></i> Tìm kiếm
              </button>
            </div>
          </div>

          <div class="row searchProduct">
            <div class="col-md-3">
              <label for="type" class="form-label mb-1 label-filter-admin-product">Loại mã</label>
              <select id="type" name="type" class="form-select setupSelect2">
                <option value="">-- Tất cả loại --</option>
                <option value="percent" {{ request('type') === 'percent' ? 'selected' : '' }}>Giảm theo %</option>
                <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Giảm trực tiếp</option>
                <option value="shipping" {{ request('type') === 'shipping' ? 'selected' : '' }}>Giảm phí vận chuyển</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="status" class="form-label mb-1 label-filter-admin-product">Trạng thái</label>
              <select id="status" name="status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Đang hoạt động</option>
                <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Không hoạt động</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="start_from" class="form-label mb-1 label-filter-admin-product">Bắt đầu từ</label>
              <input
                id="start_from"
                type="date"
                name="start_from"
                class="form-control"
                value="{{ request('start_from') }}">
            </div>

            <div class="col-md-3">
              <label for="start_to" class="form-label mb-1 label-filter-admin-product">Bắt đầu đến</label>
              <input
                id="start_to"
                type="date"
                name="start_to"
                class="form-control"
                value="{{ request('start_to') }}">
            </div>
          </div>
        </div>
      </form>

      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách mã giảm giá</h5>

        <div class="d-flex gap-2">
          <button
            type="button"
            class="btn-admin"
            data-bs-toggle="modal"
            data-bs-target="#discountCreateModal">
            <i class="fa fa-plus me-1"></i> Thêm mã giảm giá
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="checkAllWidth"><input type="checkbox" disabled></th>
              <th class="STT_Width">#</th>
              <th>MÃ GIẢM GIÁ</th>
              <th>LOẠI</th>
              <th>GIÁ TRỊ</th>
              <th>ĐƠN TỐI THIỂU</th>
              <th>GIỚI HẠN TỔNG</th>
              <th>GIỚI HẠN / USER</th>
              <th>ĐÃ DÙNG</th>
              <th>NGÀY BẮT ĐẦU</th>
              <th>NGÀY KẾT THÚC</th>
              <th>TRẠNG THÁI</th>
              <th class="actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($discounts->count() > 0): ?>
              <?php foreach ($discounts as $idx => $discount): ?>
                <?php
                $type = $discount->type;
                $status = strtoupper($discount->status ?? '');
                $now = now();
                $isExpired = $discount->end_date && $discount->end_date->lt($now);
                $isNotStarted = $discount->start_date && $discount->start_date->gt($now);

                /** @var \App\Models\Discount $discount */
                $usageLimit = $discount->usage_limit;
                $perUserLimit = $discount->per_user_limit;
                $totalUsed = $discount->total_used ?? 0;
                ?>
                <tr>
                  <td>
                    <input type="checkbox" value="{{ $discount->id }}" disabled>
                  </td>
                  <td>{{ ($discounts->firstItem() ?? 0) + $idx }}</td>
                  <td class="fw-semibold">
                     {{ $discount->code }}
                  </td>
                  <td>
                    @if($type === 'percent')
                    Giảm theo %
                    @elseif($type === 'fixed')
                    Giảm trực tiếp
                    @elseif($type === 'shipping')
                    Giảm phí vận chuyển
                    @else
                    Khác
                    @endif
                  </td>
                  <td>
                    @if($type === 'percent')
                    {{ $discount->value }}%
                    @else
                    {{ number_format($discount->value, 0, ',', '.') }} VNĐ
                    @endif
                  </td>
                  <td>
                    @if($discount->min_order_value_vnd)
                    {{ number_format($discount->min_order_value_vnd, 0, ',', '.') }} VNĐ
                    @else
                    -
                    @endif
                  </td>
                  {{-- Giới hạn tổng --}}
                  <td>
                    <?php if ($usageLimit !== null) { ?>
                      {{ $usageLimit }}
                    <?php } else { ?>
                      Không giới hạn
                    <?php } ?>
                  </td>
                  {{-- Giới hạn mỗi user --}}
                  <td>
                    <?php if ($perUserLimit !== null) { ?>
                      {{ $perUserLimit }}
                    <?php } else { ?>
                      Không giới hạn
                    <?php } ?>
                  </td>
                  {{-- Đã dùng --}}
                  <td>
                    {{ $totalUsed }}
                    <?php if ($usageLimit !== null) { ?>
                      / {{ $usageLimit }}
                    <?php } ?>
                  </td>
                  <td>
                    {{ $discount->start_date ? $discount->start_date->format('d/m/Y H:i') : '-' }}
                  </td>
                  <td>
                    {{ $discount->end_date ? $discount->end_date->format('d/m/Y H:i') : '-' }}
                  </td>
                  <td>
                    @if($isExpired)
                    <span class="badge rounded-pill badge-status badge-status--danger">
                      Hết hạn
                    </span>
                    @elseif($isNotStarted)
                    <span class="badge rounded-pill badge-status badge-status--warning">
                      Chưa hiệu lực
                    </span>
                    @elseif($status === 'ACTIVE')
                    <span class="badge rounded-pill badge-status badge-status--success">
                      Đang hoạt động
                    </span>
                    @elseif($status === 'INACTIVE')
                    <span class="badge rounded-pill badge-status badge-status--secondary">
                      Không hoạt động
                    </span>
                    @else
                    <span class="badge rounded-pill badge-status badge-status--primary">
                      Không xác định
                    </span>
                    @endif
                  </td>
                  <td class="text-center">
                    <button
                      type="button"
                      class="btn btn-sm btn-success me-2 js-edit-discount-btn"
                      title="Chỉnh sửa"
                      data-id="{{ $discount->id }}"
                      data-code="{{ $discount->code }}"
                      data-type="{{ $discount->type }}"
                      data-value="{{ $discount->value }}"
                      data-min-order="{{ $discount->min_order_value_vnd }}"
                      data-usage-limit="{{ $discount->usage_limit }}"
                      data-per-user-limit="{{ $discount->per_user_limit }}"
                      data-start="{{ $discount->start_date ? $discount->start_date->format('Y-m-d\TH:i') : '' }}"
                      data-end="{{ $discount->end_date ? $discount->end_date->format('Y-m-d\TH:i') : '' }}"
                      data-status="{{ $discount->status }}"
                      data-update-url="{{ route('admin.discount.update', $discount->id) }}">
                      <i class="fa fa-edit"></i>
                    </button>

                    <form
                      method="POST"
                      action="{{ route('admin.discount.destroy', $discount->id) }}"
                      class="d-inline discountDeleteForm">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger btnDiscountDelete">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="13" class="text-center text-muted">
                  Không có mã giảm giá nào
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $discounts->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

@include('partials.ui.discount.create-modal')
@include('partials.ui.discount.edit-modal')
@include('partials.ui.confirm-modal')
@endsection

@push('scripts')
@vite('resources/js/pages/discount.js')
@endpush