@extends('layouts.user')
@section('title', $product->title)

@section('content')
<div id="productPage" class="page-content">
  <div class="container py-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
        <li class="breadcrumb-item" aria-current="page">
          Chi tiết sản phẩm / <span class="breadcrumb-active">{{ $product->title }}</span>
        </li>
      </ol>
    </nav>

    @php
    $rating = (float)($product->rating_avg ?? 0);
    $reviews = (int)($product->reviews_count ?? 0);
    @endphp

    <div class="row g-5">
      <div class="col-lg-6">
        <div id="productCover"
          class="book-detail-cover @if(($product->quantity_available ?? 0) <= 0) is-out @endif">
          <img
            src="{{ asset('storage/products/'.$product->image) }}"
            alt="{{ $product->title }}"
            class="w-100">
          @if(($product->quantity_available ?? 0) <= 0)
            <span class="oos-badge">Tạm thời hết hàng</span>
            @endif
        </div>
      </div>

      <div class="col-lg-6">
        <h1 id="productTitleDetail" class="display-5 fw-bold mb-3">
          {{ $product->title }}
        </h1>

        <p class="lead text-muted mb-2">
          Tác giả:
          <span id="productAuthorDetail">
            {{ $product->authors?->pluck('name')->join(', ') ?: 'Đang cập nhật' }}
          </span>
        </p>

        <p class="text-muted mb-4">
          Nhà xuất bản:
          <span>{{ $product->publisher->name ?? 'Đang cập nhật' }}</span>
        </p>

        <div class="mb-4">
          <span class="h2 text-danger me-3" id="productPrice">
            {{ number_format((int)($product->selling_price_vnd ?? 0), 0, ',', '.') }}đ
          </span>
          @if(!empty($product->original_price_vnd) && (int)$product->original_price_vnd > (int)($product->selling_price_vnd ?? 0))
          <span class="h5 text-muted text-decoration-line-through" id="productOriginalPrice">
            {{ number_format((int)$product->original_price_vnd, 0, ',', '.') }}đ
          </span>
          @endif
        </div>

        <div class="mb-4">
          <div class="rating mb-2">
            @if ($reviews > 0)
            @for ($i = 1; $i <= 5; $i++)
              <i class="fas fa-star {{ $rating >= $i ? 'text-warning' : 'text-secondary' }}"></i>
              @endfor
              <span class="ms-2">({{ number_format($rating, 1) }}/5 - {{ $reviews }} đánh giá)</span>
              @else
              <span class="text-rating">Chưa có đánh giá</span>
              @endif
          </div>
          <div class="text-muted">
            Tồn kho: <strong>{{ (int)($product->quantity_available ?? 0) }}</strong>
            @if(($product->quantity_available ?? 0) <= 0)
              <span class="badge bg-secondary ms-2">Hết hàng</span>
              @endif
          </div>
          @if($product->categories?->count())
          <div class="text-muted mt-2">
            Thể loại:
            <span>{{ $product->categories->pluck('name')->join(', ') }}</span>
          </div>
          @endif
        </div>

        <div class="mb-4">
          <h5>Mô tả sản phẩm</h5>
          <p class="mb-2">{{ $product->description ?: 'Mô tả đang được cập nhật.' }}</p>
          <ul class="list-unstyled">
            <li><i class="fas fa-check text-success me-2"></i>Chính hãng, mới 100%</li>
            <li><i class="fas fa-check text-success me-2"></i>Đổi trả theo chính sách cửa hàng</li>
            <li><i class="fas fa-check text-success me-2"></i>Đóng gói cẩn thận</li>
            <li><i class="fas fa-check text-success me-2"></i>Giao hàng toàn quốc</li>
          </ul>
        </div>

        <div class="mb-4">
          <label class="form-label">Số lượng:</label>
          <div class="quantity-control" id="qtyBox">
            <button class="btn btn-outline-secondary" type="button" data-delta="-1">-</button>
            <input id="quantity" type="number" class="form-control text-center"
              value="1" min="1" max="{{ $product->quantity_available }}">
            <button class="btn btn-outline-secondary" type="button" data-delta="1">+</button>
          </div>
        </div>


        <div class="d-grid gap-2 d-md-flex">
          @php $out = (($product->quantity_available ?? 0) <= 0); @endphp
            <button
            class="btn {{ $out ? 'btn-secondary disabled' : 'btn-primary' }} btn-lg flex-fill"
            type="button"
            {{ $out ? 'disabled aria-disabled=true' : '' }}>
            <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
            </button>


            @php $isFav = (bool)($product->is_favorite ?? false); @endphp
            <button
              type="button"
              class="btn btn-lg {{ $isFav ? 'btn-danger' : 'btn-outline-danger' }} js-fav-toggle"
              data-id="{{ $product->id }}"
              data-add-url="{{ route('addFavoriteProduct') }}"
              data-del-url="{{ route('destroyFavoriteProduct', '__ID__') }}"
              aria-pressed="{{ $isFav ? 'true' : 'false' }}">
              {{-- Chọn 1 hệ icon thôi. Ví dụ: Font Awesome --}}
              <i class="{{ $isFav ? 'fas fa-heart' : 'far fa-heart' }} me-2" aria-hidden="true"></i>
              <span class="js-fav-label">{{ $isFav ? 'Bỏ thích' : 'Yêu thích' }}</span>
            </button>
        </div>

        <input type="hidden" id="productId" value="{{ $product->id }}">
      </div>
    </div>

    <hr class="my-5">

    <section class="product-reviews">
      <div class="reviews-head d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0 review-title">Đánh giá của người dùng</h3>
        <a href="#" class="reviews-view-all">Xem tất cả</a>
      </div>

      <div class="reviews-list">
        <!-- Review #1 -->
        <article class="review-card">
          <div class="review-header">
            <div class="review-left">
              <div class="review-avatar">N</div>
              <div class="review-author">
                <strong class="review-name">Nguyễn Minh</strong>
                <div class="review-date">12/07/2025</div>
              </div>
            </div>
            <div class="review-right">
              <div class="review-stars" aria-label="4 trên 5 sao">
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <div class="review-body">
            Nội dung sách gần gũi, đọc rất “thấm”. Bản in đẹp, giấy dày, giao nhanh.
          </div>
        </article>

        <!-- Review #2 -->
        <article class="review-card">
          <div class="review-header">
            <div class="review-left">
              <div class="review-avatar">H</div>
              <div class="review-author">
                <strong class="review-name">Hoài Phương</strong>
                <div class="review-date">03/06/2025</div>
              </div>
            </div>
            <div class="review-right">
              <div class="review-stars" aria-label="5 trên 5 sao">
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
              </div>
            </div>
          </div>
          <div class="review-body">
            Mua tặng bạn, bản này bìa rất đẹp. Nội dung truyền cảm hứng, đáng đọc!
          </div>
          <div class="review-reply">
            <div class="reply-title">Phản hồi từ cửa hàng</div>
            <div class="reply-body">Cảm ơn bạn đã tin tưởng. Hẹn gặp lại ở các đơn kế tiếp!</div>
          </div>
        </article>

        <!-- Review #3 (ví dụ khi chưa có nhiều nội dung) -->
        <article class="review-card">
          <div class="review-header">
            <div class="review-left">
              <div class="review-avatar">A</div>
              <div class="review-author">
                <strong class="review-name">Anh Tuấn</strong>
                <div class="review-date">20/05/2025</div>
              </div>
            </div>
            <div class="review-right">
              <div class="review-stars" aria-label="3 trên 5 sao">
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star filled"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <div class="review-body">
            Sách ổn, đóng gói chắc chắn. Mình mong có thêm vài phụ lục tham khảo.
          </div>
        </article>
      </div>

      <!-- Nếu chưa có đánh giá nào, thay cả .reviews-list bằng đoạn này:
  <div class="reviews-empty text-muted">Chưa có đánh giá nào</div>
  -->
    </section>


  </div>
</div>


@endsection