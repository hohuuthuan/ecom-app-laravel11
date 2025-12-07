@extends('layouts.admin')

@section('title','Reviews: Đánh giá sản phẩm')

@section('body_class','review-product-page')

@section('content')
@php
$tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
@endphp

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="{{ route('admin.dashboard') }}">Admin</a>
    </li>
    <li class="breadcrumb-item">
      <a href="{{ route('admin.review.index') }}">Danh sách sản phẩm có đánh giá</a>
    </li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">
      Đánh giá: {{ $product->title }}
    </li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-2 mb-3">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        @if($product->image)
        <img
          src="{{ asset('storage/products/'.$product->image) }}"
          alt="{{ $product->title }}"
          class="img-fluid mb-3"
          style="max-height: 220px; object-fit: contain;"
          loading="lazy">
        @else
        <div class="text-muted mb-3">Không có ảnh sản phẩm</div>
        @endif

        <h5 class="fw-semibold mb-2">{{ $product->title }}</h5>

        @if($stats)
        <div class="mt-3 text-start small">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Tổng đánh giá:</span>
            <span class="fw-semibold">{{ (int)($stats->total_reviews ?? 0) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Đang hiển thị (ACTIVE):</span>
            <span class="fw-semibold text-success">{{ (int)($stats->active_reviews ?? 0) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Chưa duyệt / Ẩn (INACTIVE):</span>
            <span class="fw-semibold text-warning">{{ (int)($stats->inactive_reviews ?? 0) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Rating trung bình:</span>
            <span class="fw-semibold">
              @php($avg = $stats->avg_rating)
              @if($avg !== null)
              {{ number_format($avg, 1) }} / 5
              @else
              —
              @endif
            </span>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-10">
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Danh sách đánh giá</h5>

          <form method="GET" class="d-flex align-items-center">
            <div class="me-2">
              <select name="status" class="setupSelect2">
                @php($curStatus = request('status'))
                <option value="">Tất cả trạng thái</option>
                <option value="ACTIVE" {{ $curStatus === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                <option value="INACTIVE" {{ $curStatus === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
              </select>
            </div>

            @php($pr = (int)request('per_page_review', 12))
            <div class="me-2">
              <select name="per_page_review" class="setupSelect2">
                <option value="6" {{ $pr === 6 ? 'selected' : '' }}>6</option>
                <option value="12" {{ $pr === 12 ? 'selected' : '' }}>12</option>
                <option value="24" {{ $pr === 24 ? 'selected' : '' }}>24</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
              <i class="fa fa-filter me-1"></i> Lọc
            </button>
          </form>
        </div>

        @if($reviews->count() === 0)
        <div class="text-center text-muted py-3">
          Chưa có đánh giá nào cho sản phẩm này.
        </div>
        @else
        <div class="table-responsive">
          <table class="table table-sm align-middle review-table">
            <thead class="table-light">
              <tr>
                <th class="review-col-index">#</th>
                <th class="review-col-customer">Khách hàng</th>
                <th class="review-col-image">Ảnh</th>
                <th class="review-col-rating">Số sao</th>
                <th class="review-col-comment">Nội dung đánh giá</th>
                <th class="review-col-reply">Phản hồi admin</th>
                <th class="review-col-action text-center">Trạng thái</th>
                <th class="review-col-action-update text-center">Cập nhật</th>
              </tr>
            </thead>
            <tbody>
              @foreach($reviews as $idx => $review)
              <tr class="review-row" data-review-id="{{ $review->id }}">
                <td>{{ ($reviews->firstItem() ?? 0) + $idx }}</td>
                <td class="review-customer-info">
                  <div class="fw-semibold">
                    {{ $review->user->name ?? 'Khách' }}
                  </div>
                  <div class="small text-muted">
                    {{ $review->user->email ?? '—' }}
                  </div>
                  <div class="small text-muted mt-1">
                    @if($review->created_at)
                    {{ optional($review->created_at)->timezone($tz)->format('d/m/Y H:i') }}
                    @else
                    Không rõ
                    @endif
                  </div>
                </td>
                <td>
                  @if($review->image_url ?? null)
                  <a href="{{ $review->image_url }}" target="_blank" class="d-inline-block">
                    <img
                      src="{{ $review->image_url }}"
                      alt="Ảnh đánh giá"
                      class="review-thumb-img"
                      loading="lazy">
                  </a>
                  @else
                  <span class="text-muted small">Không có</span>
                  @endif
                </td>
                <td>
                  <div>
                    @for($i = 1; $i <= 5; $i++)
                      @if($i <= (int)$review->rating)
                        <i class="fa fa-star text-warning"></i>
                      @else
                        <i class="fa fa-star text-secondary"></i>
                      @endif
                    @endfor
                  </div>
                  <div class="small text-muted mt-1">
                    {{ (int)$review->rating }} / 5
                  </div>
                </td>
                <td>
                  @if($review->comment)
                  <div class="review-comment-text">
                    {{ $review->comment }}
                  </div>
                  @else
                  <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  <textarea
                    class="form-control form-control-sm"
                    rows="2"
                    name="reply">{{ $review->reply }}</textarea>
                </td>

                {{-- Trạng thái --}}
                <td class="text-center">
                  @php($status = $review->is_active ? 'ACTIVE' : 'INACTIVE')
                  <div>
                    <select name="status" class="setupSelect2">
                      <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                      <option value="INACTIVE" {{ $status === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                    </select>
                  </div>
                </td>

                {{-- Cập nhật + thông báo --}}
                <td class="text-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-primary btn-update-review"
                    data-review-id="{{ $review->id }}">
                    Cập nhật
                  </button>
                  <div class="review-update-message small mt-1 d-none"></div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          {{ $reviews->links('pagination::bootstrap-5') }}
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';

    document.querySelectorAll('.btn-update-review').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const reviewId = this.getAttribute('data-review-id');
        const row = this.closest('.review-row');
        if (!row || !reviewId) {
          return;
        }

        const replyEl = row.querySelector('textarea[name="reply"]');
        const statusEl = row.querySelector('select[name="status"]');
        const messageEl = row.querySelector('.review-update-message');

        const reply = replyEl ? replyEl.value : '';
        const status = statusEl ? statusEl.value : 'ACTIVE';

        if (messageEl) {
          messageEl.classList.add('d-none');
          messageEl.classList.remove('text-success', 'text-danger');
          messageEl.textContent = '';
        }

        fetch("{{ route('admin.review.update', ':id') }}".replace(':id', reviewId), {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
              reply: reply,
              status: status
            })
          })
          .then(function(res) {
            return res.json();
          })
          .then(function(res) {
            if (!messageEl) {
              return;
            }

            if (!res || !res.success) {
              messageEl.textContent = res && res.message ? res.message : 'Cập nhật thất bại.';
              messageEl.classList.remove('d-none');
              messageEl.classList.add('text-danger');
              return;
            }

            messageEl.textContent = 'Cập nhật thành công';
            messageEl.classList.remove('d-none');
            messageEl.classList.add('text-success');

            setTimeout(function() {
              messageEl.classList.add('d-none');
            }, 2500);
          })
          .catch(function() {
            if (!messageEl) {
              return;
            }
            messageEl.textContent = 'Có lỗi xảy ra, vui lòng thử lại.';
            messageEl.classList.remove('d-none');
            messageEl.classList.add('text-danger');
          });
      });
    });
  });
</script>
@endpush
