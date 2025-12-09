@extends('layouts.user')
@section('title','Đánh giá sản phẩm')

@section('content')
@php
$tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
$favIds = auth()->check()
? auth()->user()->favorites()->pluck('products.id')->all()
: [];
$reviewsByProduct = $reviewsByProduct ?? collect();
@endphp

<div class="container my-4 py-2 review-order-page">
  <div class="page-header">
    <div class="page-nav">
      <a
        class="btn continue-shopping"
        href="{{ route('user.profile.index', ['tab' => 'orders']) }}">
        <i class="bi bi-arrow-left"></i>
        Quay lại lịch sử đơn hàng
      </a>
    </div>
    <h1 class="pageTitle d-flex align-items-center gap-2">
      <i class="bi bi-star-fill"></i>
      ĐÁNH GIÁ SẢN PHẨM
    </h1>
  </div>

  <div id="orderReviewPage">
    {{-- THÔNG TIN ĐƠN HÀNG --}}
    <div class="review-order-summary mb-4">
      <div class="review-order-summary-header">
        <h2 class="review-order-summary-title">
          Thông tin đơn hàng
        </h2>
      </div>
      <div class="review-order-summary-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="review-summary-item">
              <div class="review-summary-label">Mã đơn</div>
              <div class="review-summary-value">
                {{ $order->code ?? 'N/A' }}
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="review-summary-item">
              <div class="review-summary-label">Ngày đặt</div>
              <div class="review-summary-value">
                @if(!empty($order->placed_at))
                {{ optional($order->placed_at)->timezone($tz)->format('d/m/Y H:i') }}
                @else
                Không rõ
                @endif
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="review-summary-item">
              <div class="review-summary-label">Tổng tiền</div>
              <div class="review-summary-value">
                {{ number_format($order->grand_total_vnd ?? 0, 0, ',', '.') }}đ
              </div>
            </div>
          </div>

          <div class="col-md-4">
            @php
            $statusRaw = strtoupper($order->status ?? '');
            $statusLabel = 'Không xác định';
            $badgeClass = 'badge-status-pending';

            if (in_array($statusRaw, [
            'PENDING',
            'PROCESSING',
            'PICKING',
            'SHIPPING',
            'CONFIRMED',
            'SHIPPED',
            ], true)) {
            $statusLabel = 'Đang xử lý';
            $badgeClass = 'badge-status-pending';
            } elseif (in_array($statusRaw, ['DELIVERED', 'COMPLETED'], true)) {
            $statusLabel = 'Hoàn thành';
            $badgeClass = 'badge-status-success';
            } elseif (in_array($statusRaw, ['CANCELLED', 'RETURNED', 'DELIVERY_FAILED'], true)) {
            $statusLabel = 'Đã hủy / giao thất bại';
            $badgeClass = 'badge-status-cancel';
            }

            @endphp
            <div class="review-summary-item">
              <div class="review-summary-label">Trạng thái</div>
              <div class="review-summary-value">
                <span class="badge-status {{ $badgeClass }}">
                  {{ $statusLabel }}
                </span>
              </div>
            </div>
          </div>

          <div class="col-md-8">
            <div class="review-summary-item">
              <div class="review-summary-label">Ghi chú</div>
              <div class="review-summary-value text-muted">
                Hãy đánh giá trải nghiệm của bạn với từng sản phẩm trong đơn hàng này.
                Mỗi sản phẩm có thể đính kèm tối đa 1 hình minh hoạ.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- DANH SÁCH SẢN PHẨM CẦN ĐÁNH GIÁ --}}
    <div class="review-products-wrapper">
      <h2 class="review-products-title mb-3">
        <i class="icon-selected-product bi bi-bag-check-fill"></i> Sản phẩm trong đơn
      </h2>

      @forelse($order->items as $item)
      @php
      $product = $item->product ?? null;
      $imgPath = $product && $product->image
      ? asset('storage/products/'.$product->image)
      : asset('images/placeholder-120x160.svg');
      $authorNames = $product
      ? optional($product->authors)->pluck('name')->join(', ')
      : null;
      $isFav = $product && auth()->check()
      ? in_array($product->id, $favIds, true)
      : false;
      $sellingPrice = (int)($product->selling_price_vnd ?? $item->unit_price_vnd ?? 0);
      $listedPrice = (int)($product->listed_price_vnd ?? 0);

      $existingReview = $product
      ? ($reviewsByProduct[$product->id] ?? null)
      : null;
      @endphp

      <div class="review-product-card {{ $existingReview ? 'review-product-card--done' : '' }}">
        <div class="row g-3 align-items-stretch review-product-main">
          {{-- CỘT TRÁI: CARD SÁCH --}}
          <div class="col-lg-4 col-md-5">
            <div class="card book-card book-card-review">
              <div class="book-cover">
                <img
                  src="{{ $imgPath }}"
                  alt="{{ $product->title ?? ($item->product_name ?? 'Sản phẩm') }}"
                  class="book-cover-img"
                  loading="lazy">
              </div>

              <div class="card-body d-flex flex-column">
                <h6 class="card-title line-clamp-2 mb-1">
                  @if($product)
                  <a
                    href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}"
                    class="text-body text-decoration-none">
                    {{ $product->title }}
                  </a>
                  @else
                  <span class="text-body">
                    {{ $item->product_name ?? 'Sản phẩm đã bị xoá' }}
                  </span>
                  @endif
                </h6>
                <p class="card-text text-muted mb-3">
                  {{ $authorNames ?: 'Không rõ tác giả' }}
                </p>

                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <span class="price">
                      {{ number_format($sellingPrice, 0, ',', '.') }} VNĐ
                    </span>
                    @if($listedPrice > $sellingPrice)
                    <small class="text-muted text-decoration-line-through ms-2">
                      {{ number_format($listedPrice, 0, ',', '.') }} VNĐ
                    </small>
                    @endif
                  </div>

                  @if($product)
                  @auth
                  <button
                    type="button"
                    class="btn btn-sm {{ $isFav ? 'btn-danger' : 'btn-outline-danger' }} js-fav-toggle"
                    data-id="{{ $product->id }}"
                    data-add-url="{{ route('addFavoriteProduct') }}"
                    data-del-url="{{ route('destroyFavoriteProduct', '__ID__') }}"
                    aria-pressed="{{ $isFav ? 'true' : 'false' }}">
                    <i class="bi {{ $isFav ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                    <span class="js-fav-label">{{ $isFav ? 'Bỏ thích' : 'Yêu thích' }}</span>
                  </button>
                  @else
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger js-fav-login-required">
                    <i class="bi bi-heart"></i>
                    <span>Yêu thích</span>
                  </button>
                  @endauth
                  @endif
                </div>

                <div class="mt-auto d-grid gap-2">
                  @if($product)
                  <a
                    href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}"
                    class="btn btn-outline-primary">
                    <i class="fas fa-eye me-2"></i>Xem chi tiết
                  </a>

                  <form
                    action="{{ route('cart.item.add') }}"
                    method="post"
                    class="add-to-cart-form"
                    data-no-loading>
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-primary w-100">
                      <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
                    </button>
                  </form>
                  @else
                  <button
                    type="button"
                    class="btn btn-outline-secondary"
                    disabled>
                    Sản phẩm không còn khả dụng
                  </button>
                  @endif
                </div>
              </div>
            </div>
          </div>

          {{-- CỘT PHẢI --}}
          <div class="col-lg-8 col-md-7">
            @if(!$product)
            <p class="text-muted mb-0">
              Sản phẩm đã bị xoá, không thể gửi đánh giá.
            </p>
            @elseif($existingReview)
            {{-- ĐÃ ĐÁNH GIÁ: hiển thị read-only, KHÔNG cho gửi lại --}}
            <div class="review-product-right review-product-right--readonly d-flex flex-column">
              <div class="mb-2 d-flex align-items-center justify-content-between">
                <div class="fw-semibold text-success d-flex align-items-center gap-2">
                  <i class="bi bi-check-circle-fill"></i>
                  Bạn đã đánh giá sản phẩm này
                </div>
                <div class="small text-muted">
                  {{ optional($existingReview->created_at)->timezone($tz)->format('d/m/Y H:i') }}
                </div>
              </div>

              <div class="review-rating-block mb-2">
                <div class="review-rating-label mb-1">
                  Đánh giá của bạn
                </div>
                <div class="review-rating-stars review-rating-stars-readonly">
                  @for ($star = 1; $star <= 5; $star++)
                    <span class="star {{ $star <= $existingReview->rating ? 'is-active' : '' }}">★</span>
                    @endfor
                </div>
              </div>

              @if($existingReview->comment)
              <div class="mb-2">
                <div class="review-textarea-label mb-1">
                  Nhận xét
                </div>
                <div class="review-comment-readonly">
                  {{ $existingReview->comment }}
                </div>
              </div>
              @endif

              @if($existingReview->image_url)
              <div class="mt-1">
                <div class="review-upload-label mb-1">
                  Hình minh hoạ
                </div>
                <div class="review-upload-preview">
                  <div class="review-upload-preview-box">
                    <img
                      src="{{ $existingReview->image_url }}"
                      alt="Ảnh review"
                      class="review-upload-preview-img">
                  </div>
                </div>
              </div>
              @endif
            </div>
            @else
            {{-- CHƯA ĐÁNH GIÁ: hiển thị form --}}
            <form
              class="review-product-right d-flex flex-column js-review-item-form"
              method="POST"
              action="{{ route('user.reviews.storeFromOrder') }}"
              enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="order_id" value="{{ $order->id }}">
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <input type="hidden" name="order_item_id" value="{{ $item->id }}">

              {{-- Hộp báo lỗi / thành công --}}
              <div class="review-message-box js-review-message d-none"></div>

              {{-- Rating sao --}}
              <div class="review-rating-block mb-2">
                <div class="review-rating-label mb-1">
                  Đánh giá của bạn
                </div>
                <div class="review-rating-stars" data-product-id="{{ $product->id }}">
                  @for ($star = 5; $star >= 1; $star--)
                  @php
                  $inputId = 'rating_'.$item->id.'_'.$star;
                  @endphp
                  <input
                    type="radio"
                    id="{{ $inputId }}"
                    name="rating"
                    value="{{ $star }}">
                  <label for="{{ $inputId }}" title="{{ $star }} sao">
                    ★
                  </label>
                  @endfor
                </div>
              </div>

              {{-- Nội dung đánh giá --}}
              <div class="mb-2">
                <label class="review-textarea-label mb-1">
                  Nhận xét của bạn
                </label>
                <textarea
                  name="comment"
                  class="form-control review-textarea"
                  rows="3"
                  placeholder="Chia sẻ cảm nhận về sản phẩm (chất lượng, nội dung, đóng gói, giao hàng, ...)"></textarea>
              </div>

              {{-- Upload hình + preview + nút gửi --}}
              <div class="mt-1">
                <label class="review-upload-label mb-1">
                  Hình ảnh
                </label>

                <div class="review-upload-row d-flex flex-wrap align-items-center gap-3">
                  <div class="review-upload-block">
                    <div class="review-upload-input-wrapper">
                      <input
                        type="file"
                        name="image"
                        class="form-control form-control-sm review-upload-input"
                        accept="image/*">
                    </div>
                    <small class="text-muted d-block mt-1">
                      Chọn 1 hình, &lt; 2MB, định dạng: JPG, PNG.
                    </small>
                  </div>

                  <div class="review-upload-preview">
                    <div class="review-upload-preview-box js-review-preview-box d-none">
                      <img src="" alt="Ảnh review" class="review-upload-preview-img">
                    </div>
                  </div>

                  <div class="ms-auto">
                    <button
                      type="button"
                      class="btn btn-sm review-submit-btn">
                      <i class="bi bi-send-fill me-1"></i>
                      Gửi đánh giá
                    </button>
                  </div>
                </div>
              </div>

            </form>
            @endif
          </div>

        </div>
      </div>
      @empty
      <p class="text-muted">
        Đơn hàng này không có sản phẩm nào để đánh giá.
      </p>
      @endforelse
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/pages/orderReview.js'])
@endpush