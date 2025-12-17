@extends('layouts.user')
@section('title', $product->title)

@section('content')
<div id="productPage" class="page-content">
  <div class="container py-5">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('home') }}">Trang chủ</a>
        </li>
        <li class="breadcrumb-item" aria-current="page">
          Chi tiết sản phẩm / <span class="breadcrumb-active">{{ $product->title }}</span>
        </li>
      </ol>
    </nav>

    @php
    $rating = (float) ($product->rating_avg ?? 0);
    $reviewsCount = (int) ($product->reviews_count ?? 0);
    $out = (int) ($product->quantity_available ?? 0) <= 0;
      $isFav=(bool) ($product->is_favorite ?? false);

      // === CHỈ THÊM PHẦN GIẢM GIÁ THEO discount_percent ===
      $discountPercent = (int) ($product->discount_percent ?? 0);
      $discountPercent = max(0, min(100, $discountPercent));

      $sellingPrice = (int) ($product->selling_price_vnd ?? 0);
      $finalPrice = $discountPercent > 0
      ? (int) round($sellingPrice * (100 - $discountPercent) / 100)
      : $sellingPrice;

      $hasPercentDiscount = $discountPercent > 0 && $finalPrice > 0 && $finalPrice < $sellingPrice;
        //===END===@endphp

        <div class="row g-5">
        <div class="col-lg-6">
          <div id="productCover" class="book-detail-cover {{ $out ? 'is-out' : '' }} position-relative">
            <img
              src="{{ asset('storage/products/'.$product->image) }}"
              alt="{{ $product->title }}"
              class="w-100">

            @if($hasPercentDiscount)
            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
              -{{ $discountPercent }}%
            </span>
            @endif

            @if($out)
            <span class="oos-badge">Tạm thời hết hàng</span>
            @endif
          </div>
        </div>

        {{-- Thông tin --}}
        <div class="col-lg-6">
          <h1 class="display-5 fw-bold mb-3">{{ $product->title }}</h1>

          <p class="lead text-muted mb-2">
            Tác giả:
            <span>
              {{ $product->authors?->pluck('name')->join(', ') ?: 'Đang cập nhật' }}
            </span>
          </p>

          <p class="text-muted mb-4">
            Nhà xuất bản:
            <span>{{ $product->publisher->name ?? 'Đang cập nhật' }}</span>
          </p>

          <div class="mb-4">
            @if($hasPercentDiscount)
            <span class="h2 text-danger me-3">
              {{ number_format($finalPrice, 0, ',', '.') }} VNĐ
            </span>
            <span class="h5 text-muted text-decoration-line-through">
              {{ number_format($sellingPrice, 0, ',', '.') }} VNĐ
            </span>
            @else
            <span class="h2 text-danger me-3">
              {{ number_format((int)($product->selling_price_vnd ?? 0), 0, ',', '.') }} VNĐ
            </span>
            @if(!empty($product->original_price_vnd) && (int)$product->original_price_vnd > (int)($product->selling_price_vnd ?? 0))
            <span class="h5 text-muted text-decoration-line-through">
              {{ number_format((int)$product->original_price_vnd, 0, ',', '.') }} VNĐ
            </span>
            @endif
            @endif
          </div>

          <div class="mb-4">
            <div class="rating mb-2">
              @if ($reviewsCount > 0)
              @for ($i = 1; $i <= 5; $i++)
                <i class="fas fa-star {{ $rating >= $i ? 'text-warning' : 'text-secondary' }}"></i>
                @endfor
                <span class="ms-2">
                  ({{ number_format($rating, 1) }}/5 - {{ $reviewsCount }} đánh giá)
                </span>
                @else
                <span class="text-rating">Chưa có đánh giá</span>
                @endif
            </div>
            <div class="text-muted">
              Tồn kho: <strong>{{ (int)($product->quantity_available ?? 0) }}</strong>
              @if($out)
              <span class="badge bg-secondary ms-2">Hết hàng</span>
              @endif
            </div>
            @if($product->categories?->count())
            <div class="text-muted mt-2">
              Thể loại: <span>{{ $product->categories->pluck('name')->join(', ') }}</span>
            </div>
            @endif
          </div>

          <div class="mb-4">
            <h5>Mô tả sản phẩm</h5>
            <p class="mb-2">{{ $product->description ?: 'Mô tả đang được cập nhật' }}</p>
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
              <input
                id="quantity"
                type="number"
                class="form-control text-center"
                value="1"
                min="1"
                max="{{ $product->quantity_available }}">
              <button class="btn btn-outline-secondary" type="button" data-delta="1">+</button>
            </div>
          </div>

          <div class="d-grid gap-2 d-md-flex">
            <form
              id="addToCartForm"
              action="{{ route('cart.item.add') }}"
              method="POST"
              class="flex-fill d-flex add-to-cart-form"
              data-no-loading>
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <input type="hidden" name="qty" id="addToCartQty" value="1">
              <button
                type="submit"
                class="btn {{ $out ? 'btn-secondary disabled' : 'btn-primary' }} btn-lg w-100"
                {{ $out ? 'disabled aria-disabled=true' : '' }}>
                <i class="fas fa-cart-plus me-2" aria-hidden="true"></i>Thêm vào giỏ
              </button>
            </form>

            @auth
            <button
              type="button"
              class="btn btn-lg {{ $isFav ? 'btn-danger' : 'btn-outline-danger' }} js-fav-toggle"
              data-id="{{ $product->id }}"
              data-add-url="{{ route('addFavoriteProduct') }}"
              data-del-url="{{ route('destroyFavoriteProduct', '__ID__') }}"
              aria-pressed="{{ $isFav ? 'true' : 'false' }}"
              id="btnFavorite">
              <i class="{{ $isFav ? 'fas fa-heart' : 'far fa-heart' }} me-2" aria-hidden="true"></i>
              <span class="js-fav-label">{{ $isFav ? 'Bỏ thích' : 'Yêu thích' }}</span>
            </button>
            @else
            <button
              type="button"
              class="btn btn-lg btn-outline-danger js-fav-login-required"
              id="btnFavoriteGuest">
              <i class="far fa-heart me-2" aria-hidden="true"></i>
              <span>Yêu thích</span>
            </button>
            @endauth
          </div>

          <input type="hidden" id="productId" value="{{ $product->id }}">
        </div>
  </div>

  <hr class="my-5">
  <section class="product-reviews" id="productReviews">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="h5 mb-0 review-title">Đánh giá của người dùng</h3>

      <form class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        <select
          class="form-select form-select-sm w-auto"
          name="per_page_review">
          <option value="4" {{ ($perPageReview ?? 8) === 4  ? 'selected' : '' }}>4</option>
          <option value="8" {{ ($perPageReview ?? 8) === 8  ? 'selected' : '' }}>8</option>
          <option value="12" {{ ($perPageReview ?? 8) === 12 ? 'selected' : '' }}>12</option>
        </select>
      </form>
    </div>

    <div
      id="reviewsContainer"
      data-reviews-container
      data-reviews-url="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}">
      @include('partials.ui.productDetail.reviews-list', ['reviews' => $reviews])
    </div>
  </section>

  <hr class="my-5">

  <section class="product-related mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="h5 mb-0 review-title">Sản phẩm liên quan</h3>

      <form class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        <select
          class="form-select form-select-sm w-auto"
          name="per_page_related">
          <option value="6" {{ ($perPageRelated ?? 6) === 6  ? 'selected' : '' }}>6</option>
          <option value="8" {{ ($perPageRelated ?? 8) === 8  ? 'selected' : '' }}>8</option>
          <option value="12" {{ ($perPageRelated ?? 8) === 12 ? 'selected' : '' }}>12</option>
        </select>
      </form>
    </div>

    <div
      id="relatedProductsWrapper"
      data-related-container
      data-related-url="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}">
      @include('partials.ui.productDetail.related-products', [
      'products' => $relatedProducts,
      'perPageRelated' => $perPageRelated ?? 8,
      ])
    </div>
  </section>
</div>
</div>

<section class="newsletter-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center">
        <h2 class="display-5 fw-bold mb-4">Đăng ký nhận tin</h2>
        <p class="lead mb-4">Nhận thông báo về sách mới, ưu đãi đặc biệt và các sự kiện thú vị</p>

        <form class="row g-3 justify-content-center" onsubmit="subscribeNewsletter(event)">
          <div class="col-md-6">
            <input type="email" class="form-control form-control-lg"
              placeholder="Nhập email của bạn" required>
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-warning btn-lg">
              <span class="button-text">Đăng ký</span>
              <div class="loading-spinner"></div>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@include('partials.ui.productDetail.noti-modal')

@push('scripts')
<script>
  // PHÂN TRANG REVIEW = AJAX, GIỐNG SẢN PHẨM LIÊN QUAN
  document.addEventListener('DOMContentLoaded', function() {
    var reviewsContainer = document.querySelector('[data-reviews-container]');
    if (!reviewsContainer) {
      return;
    }

    var baseUrl = reviewsContainer.getAttribute('data-reviews-url') || window.location.href;

    function buildReviewsUrl(rawUrl) {
      var url = rawUrl || baseUrl;
      var urlObj = new URL(url, window.location.origin);

      // Flag cho controller biết chỉ trả partial review
      urlObj.searchParams.set('reviews_only', '1');

      // Nếu sau này bạn có per_page_review thì set thêm ở đây (giống per_page_related)
      return urlObj.toString();
    }

    function loadReviews(url) {
      if (!url) {
        return;
      }

      // Nếu muốn có loading overlay cho review thì thêm class ở đây
      fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(function(res) {
          if (!res.ok) {
            throw new Error('Request failed');
          }
          return res.text();
        })
        .then(function(html) {
          reviewsContainer.innerHTML = html;
        })
        .catch(function() {
          // có thể log error nếu cần
        });
    }

    // Bắt sự kiện click vào link phân trang trong khối review
    reviewsContainer.addEventListener('click', function(e) {
      var link = e.target.closest('.reviews-pagination a');
      if (!link) {
        return;
      }

      e.preventDefault();

      var href = link.getAttribute('href') || '';
      var url = buildReviewsUrl(href);

      loadReviews(url);
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var relatedWrapper = document.querySelector('[data-related-container]');
    if (!relatedWrapper) {
      return;
    }

    var baseUrl = relatedWrapper.getAttribute('data-related-url') || window.location.href;
    var perPageSelect = document.querySelector('select[name="per_page_related"]');

    function initRelatedImages() {
      var covers = relatedWrapper.querySelectorAll('.book-cover');
      covers.forEach(function(cover) {
        var img = cover.querySelector('.book-cover-img');
        if (!img) {
          return;
        }

        function markLoaded() {
          cover.classList.add('is-loaded');
        }

        if (img.complete && img.naturalWidth !== 0) {
          markLoaded();
        } else {
          img.addEventListener('load', markLoaded, {
            once: true
          });
          img.addEventListener('error', markLoaded, {
            once: true
          });
        }
      });
    }

    function buildRelatedUrl(rawUrl) {
      var url = rawUrl || baseUrl;
      var urlObj = new URL(url, window.location.origin);

      urlObj.searchParams.set('related_only', '1');

      if (perPageSelect && perPageSelect.value) {
        urlObj.searchParams.set('per_page_related', perPageSelect.value);
      }

      return urlObj.toString();
    }

    function loadRelated(url) {
      if (!url) {
        return;
      }

      relatedWrapper.classList.add('is-loading');

      fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(function(res) {
          if (!res.ok) {
            throw new Error('Request failed');
          }
          return res.text();
        })
        .then(function(html) {
          relatedWrapper.innerHTML = html;
          relatedWrapper.classList.remove('is-loading');
          initRelatedImages();
        })
        .catch(function() {
          relatedWrapper.classList.remove('is-loading');
        });
    }

    initRelatedImages();

    if (perPageSelect) {
      perPageSelect.addEventListener('change', function(e) {
        e.preventDefault();

        var base = buildRelatedUrl(baseUrl);
        var urlObj = new URL(base, window.location.origin);
        urlObj.searchParams.delete('page');

        loadRelated(urlObj.toString());
      });
    }

    relatedWrapper.addEventListener('click', function(e) {
      var link = e.target.closest('.related-pagination a');
      if (!link) {
        return;
      }

      e.preventDefault();

      var href = link.getAttribute('href') || '';
      var url = buildRelatedUrl(href);

      loadRelated(url);
    });
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var relatedWrapper = document.querySelector('[data-related-container]');
    if (!relatedWrapper) {
      return;
    }

    var baseUrl = relatedWrapper.getAttribute('data-related-url') || window.location.href;
    var perPageSelect = document.querySelector('select[name="per_page_related"]');

    function initRelatedImages() {
      var covers = relatedWrapper.querySelectorAll('.book-cover');
      covers.forEach(function(cover) {
        var img = cover.querySelector('.book-cover-img');
        if (!img) {
          return;
        }

        function markLoaded() {
          cover.classList.add('is-loaded');
        }

        if (img.complete && img.naturalWidth !== 0) {
          markLoaded();
        } else {
          img.addEventListener('load', markLoaded, {
            once: true
          });
          img.addEventListener('error', markLoaded, {
            once: true
          });
        }
      });
    }

    function scrollToRelated() {
      var target = relatedWrapper.closest('section.product-related') || relatedWrapper;
      var header = document.querySelector('header, .sticky-top, .navbar.sticky-top');
      var offset = header ? header.offsetHeight + 12 : 12;
      var top = target.getBoundingClientRect().top + window.pageYOffset - offset;

      window.scrollTo({
        top: top < 0 ? 0 : top,
        behavior: 'smooth'
      });
    }

    function buildRelatedUrl(rawUrl) {
      var url = rawUrl || baseUrl;
      var urlObj = new URL(url, window.location.origin);

      urlObj.searchParams.set('related_only', '1');

      if (perPageSelect && perPageSelect.value) {
        urlObj.searchParams.set('per_page_related', perPageSelect.value);
      }

      return urlObj.toString();
    }

    function loadRelated(url, shouldScroll) {
      if (!url) {
        return;
      }

      relatedWrapper.classList.add('is-loading');

      fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(function(res) {
          if (!res.ok) {
            throw new Error('Request failed');
          }
          return res.text();
        })
        .then(function(html) {
          relatedWrapper.innerHTML = html;
          relatedWrapper.classList.remove('is-loading');
          initRelatedImages();

          if (shouldScroll) {
            scrollToRelated();
          }
        })
        .catch(function() {
          relatedWrapper.classList.remove('is-loading');
        });
    }

    initRelatedImages();

    if (perPageSelect) {
      perPageSelect.addEventListener('change', function(e) {
        e.preventDefault();

        var urlObj = new URL(buildRelatedUrl(baseUrl), window.location.origin);
        urlObj.searchParams.delete('page');

        loadRelated(urlObj.toString(), true);
      });
    }

    relatedWrapper.addEventListener('click', function(e) {
      var link = e.target.closest('.related-pagination a');
      if (!link) {
        return;
      }

      e.preventDefault();
      loadRelated(buildRelatedUrl(link.getAttribute('href') || ''), true);
    });
  });
</script>


@endpush
@endsection