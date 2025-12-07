@extends('layouts.admin')

@section('title','Reviews: Danh sách sản phẩm có đánh giá')

@section('body_class','review-index-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Đánh giá</li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách sản phẩm có đánh giá</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        @php($pp = (int)request('per_page_product', 10))
        <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_product" onchange="this.form.submit()">
          <option value="10" {{ $pp===10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ $pp===20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ $pp===50 ? 'selected' : '' }}>50</option>
        </select>

        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
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
                placeholder="Tìm theo tên sản phẩm"
                value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
              <label class="d-block mb-1">&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-admin btn-submit-filter-admin-order">
                <i class="fa fa-search me-1"></i> Tìm kiếm
              </button>
            </div>
          </div>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="th-order-table STT_Width">#</th>
              <th class="th-order-table">HÌNH ẢNH</th>
              {{-- Đã xóa cột MÃ SẢN PHẨM --}}
              <th class="th-order-table">TÊN SẢN PHẨM</th>
              <th class="th-order-table text-center">TỔNG ĐÁNH GIÁ</th>
              <th class="th-order-table text-center">ĐÁNH GIÁ MỚI</th>
              <th class="th-order-table text-center">TRUNG BÌNH RATING</th>
              <th class="th-order-table actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>

          <tbody>
            @forelse($productReviewSummary as $idx => $item)
            @php($product = $item->product)
            <tr>
              <td>{{ ($productReviewSummary->firstItem() ?? 0) + $idx }}</td>
              <td>
                @if($product && $product->image)
                {{-- Đã cập nhật đường dẫn ảnh theo yêu cầu --}}
                <img
                  src="{{ asset('storage/products/'.$product->image) }}"
                  alt="{{ $product->title }}"
                  class="img-thumbnail"
                  style="max-width: 70px; max-height: 70px;"
                  loading="lazy">
                @else
                <span class="text-muted">Không có ảnh</span>
                @endif
              </td>
              {{-- Đã xóa dòng hiển thị Mã sản phẩm --}}
              <td>
                @if($product)
                <div class="fw-semibold">{{ $product->title }}</div>
                @else
                <span class="text-muted">Sản phẩm không tồn tại</span>
                @endif
              </td>
              <td class="text-center">
                {{ $item->total_reviews ?? 0 }}
              </td>
              <td class="text-center">
                @php($newCount = (int)($item->inactive_reviews ?? 0))
                @if($newCount > 0)
                <span class="badge rounded-pill bg-warning text-dark">
                  {{ $newCount }} mới
                </span>
                @else
                <span class="text-muted">0</span>
                @endif
              </td>
              <td class="text-center">
                @php($avg = $item->avg_rating)
                @if($avg !== null)
                {{ number_format($avg, 1) }} / 5
                @else
                <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-center">
                @if($product)
                <a href="{{ route('admin.review.product.show', $item->product_id) }}" title="Xem danh sách đánh giá">
                  <i class="fa fa-eye icon-eye-view-order-detail"></i>
                </a>
                @else
                <span class="text-muted">—</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              {{-- Giảm colspan từ 8 xuống 7 vì đã xóa 1 cột --}}
              <td colspan="7" class="text-center text-muted">Không có sản phẩm nào có đánh giá.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $productReviewSummary->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

@include('partials.ui.confirm-modal')
@endsection