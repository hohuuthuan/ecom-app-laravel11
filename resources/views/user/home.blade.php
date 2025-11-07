@extends('layouts.user')
@section('title','Trang chủ')

@section('content')
<div id="homePage" class="page-content">
  <!-- Hero Section -->
  <section class="hero-section" id="home">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <div class="hero-content">
            <h1 class="display-4 fw-bold mb-4">Khám phá thế giới tri thức</h1>
            <p class="lead mb-4">Hàng nghìn cuốn sách hay đang chờ bạn khám phá. Từ tiểu thuyết đến sách
              chuyên môn, tất cả đều có tại BookStore.</p>
            <div class="d-flex flex-wrap gap-3">
              <button class="btn btn-primary btn-lg" onclick="scrollToSection('categories')">
                <i class="fas fa-compass me-2"></i>Khám phá ngay
              </button>
              <button class="btn btn-outline-light btn-lg" onclick="scrollToSection('bestsellers')">
                <i class="fas fa-fire me-2"></i>Sách hot
              </button>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="text-center">
            <div class="position-relative">
              <i class="fas fa-book-open" style="font-size: 15rem; opacity: 0.3;"></i>
              <div class="position-absolute top-50 start-50 translate-middle">
                <div class="d-flex justify-content-center">
                  <div
                    class="bg-white rounded-circle p-3 shadow me-3 animate__animated animate__fadeInUp">
                    <i class="fas fa-star text-warning" style="font-size: 2rem;"></i>
                  </div>
                  <div
                    class="bg-white rounded-circle p-3 shadow animate__animated animate__fadeInUp animate__delay-1s">
                    <i class="fas fa-heart text-danger" style="font-size: 2rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats-section">
    <div class="container">
      <div class="row">
        <div class="col-md-3 col-6 mb-4">
          <div class="stat-item">
            <div class="stat-number" data-target="15000">0</div>
            <p class="mb-0">Đầu sách</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-4">
          <div class="stat-item">
            <div class="stat-number" data-target="50000">0</div>
            <p class="mb-0">Khách hàng</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-4">
          <div class="stat-item">
            <div class="stat-number" data-target="98">0</div>
            <p class="mb-0">% Hài lòng</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-4">
          <div class="stat-item">
            <div class="stat-number" data-target="24">0</div>
            <p class="mb-0">Giờ hỗ trợ</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories Section -->
  <section class="py-5 bg-light" id="categories">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold text-primary">Danh mục sách</h2>
        <p class="lead text-muted">Tìm kiếm theo sở thích của bạn</p>
      </div>

      <div class="row g-4">
        <div class="col-lg-3 col-md-6">
          <div class="category-card" onclick="filterByCategory('novel')">
            <div class="category-icon">
              <i class="fas fa-heart"></i>
            </div>
            <h5 class="fw-bold">Tiểu thuyết</h5>
            <p class="text-muted mb-0">Những câu chuyện cảm động</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="category-card" onclick="filterByCategory('business')">
            <div class="category-icon">
              <i class="fas fa-briefcase"></i>
            </div>
            <h5 class="fw-bold">Kinh doanh</h5>
            <p class="text-muted mb-0">Phát triển sự nghiệp</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="category-card" onclick="filterByCategory('psychology')">
            <div class="category-icon">
              <i class="fas fa-brain"></i>
            </div>
            <h5 class="fw-bold">Tâm lý học</h5>
            <p class="text-muted mb-0">Hiểu về con người</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="category-card" onclick="filterByCategory('children')">
            <div class="category-icon">
              <i class="fas fa-child"></i>
            </div>
            <h5 class="fw-bold">Thiếu nhi</h5>
            <p class="text-muted mb-0">Nuôi dưỡng tâm hồn trẻ</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Bestsellers Section -->
  <section class="py-5" id="bestsellers">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
          <h2 class="display-5 fw-bold text-primary">Sách bán chạy</h2>
          <p class="lead text-muted">Những cuốn sách được yêu thích nhất</p>
        </div>
        <button class="btn btn-outline-primary" onclick="loadMoreBooks()">
          Xem thêm <i class="fas fa-arrow-right ms-2"></i>
        </button>
      </div>
      @php
      $favIds = auth()->check()
      ? auth()->user()->favorites()->pluck('products.id')->all()
      : [];
      @endphp

      <div class="row g-4" id="booksContainer">
        @forelse($products as $product)
        @php
        $isFav = auth()->check()
        ? in_array($product->id, auth()->user()->favorites()->pluck('products.id')->all(), true)
        : false;
        @endphp

        <div class="col-lg-4 col-md-6">
          <div class="card book-card h-100">
            <div class="book-cover p-0">
              <img
                src="{{ asset('storage/products/'.$product->image) }}"
                alt="{{ $product->title }}"
                class="w-100">
            </div>

            <div class="card-body d-flex flex-column">
              <h6 class="card-title line-clamp-2 mb-1">{{ $product->title }}</h6>

              @php $authorNames = optional($product->authors)->pluck('name')->join(', '); @endphp
              <p class="card-text text-muted mb-3">{{ $authorNames ?: 'Không rõ tác giả' }}</p>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <span class="price">
                    {{ number_format((int)$product->selling_price_vnd, 0, ',', '.') }}đ
                  </span>
                  @if(!empty($product->listed_price_vnd) && (int)$product->listed_price_vnd > (int)$product->selling_price_vnd)
                  <small class="text-muted text-decoration-line-through ms-2">
                    {{ number_format((int)$product->listed_price_vnd, 0, ',', '.') }}đ
                  </small>
                  @endif
                </div>

                <button
                  class="btn btn-sm {{ $isFav ? 'btn-danger' : 'btn-outline-danger' }} js-fav-toggle"
                  data-id="{{ $product->id }}"
                  data-add-url="{{ route('addFavoriteProduct') }}"
                  data-del-url="{{ route('destroyFavoriteProduct', '__ID__') }}"
                  aria-pressed="{{ $isFav ? 'true' : 'false' }}">
                  <i class="bi {{ $isFav ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                  <span class="js-fav-label">{{ $isFav ? 'Bỏ thích' : 'Yêu thích' }}</span>
                </button>
              </div>

              <div class="mt-auto d-grid gap-2">
                <a href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}" class="btn btn-outline-primary">
                  <i class="fas fa-eye me-2"></i>Xem chi tiết
                </a>

                <form action="{{ route('cart.item.add') }}" method="post" class="add-to-cart-form">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="qty" value="1">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12">
          <p class="text-muted mb-0">Chưa có sản phẩm.</p>
        </div>
        @endforelse
      </div>

    </div>
  </section>

  <!-- Testimonials Section -->
  <section class="py-5 bg-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold text-primary">Khách hàng nói gì</h2>
        <p class="lead text-muted">Những phản hồi chân thực từ độc giả</p>
      </div>

      <div class="row g-4">
        <div class="col-lg-4">
          <div class="testimonial-card">
            <div class="rating mb-3">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <p class="mb-4">"BookStore có bộ sưu tập sách rất phong phú. Giao hàng nhanh, đóng gói cẩn
              thận. Tôi đã tìm được nhiều cuốn sách hay ở đây!"</p>
            <div class="d-flex align-items-center">
              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 50px; height: 50px;">
                <i class="fas fa-user text-white"></i>
              </div>
              <div>
                <h6 class="mb-0 fw-bold">Nguyễn Văn An</h6>
                <small class="text-muted">Khách hàng thân thiết</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="testimonial-card">
            <div class="rating mb-3">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <p class="mb-4">"Website dễ sử dụng, tìm kiếm sách rất tiện lợi. Giá cả hợp lý, chất lượng
              sách tốt. Sẽ tiếp tục ủng hộ BookStore!"</p>
            <div class="d-flex align-items-center">
              <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 50px; height: 50px;">
                <i class="fas fa-user text-white"></i>
              </div>
              <div>
                <h6 class="mb-0 fw-bold">Trần Thị Bình</h6>
                <small class="text-muted">Độc giả đam mê</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="testimonial-card">
            <div class="rating mb-3">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <p class="mb-4">"Dịch vụ khách hàng tuyệt vời! Nhân viên tư vấn nhiệt tình, giúp tôi chọn
              được những cuốn sách phù hợp với nhu cầu."</p>
            <div class="d-flex align-items-center">
              <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 50px; height: 50px;">
                <i class="fas fa-user text-white"></i>
              </div>
              <div>
                <h6 class="mb-0 fw-bold">Lê Minh Cường</h6>
                <small class="text-muted">Khách hàng mới</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Newsletter Section -->
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
</div>


@endsection