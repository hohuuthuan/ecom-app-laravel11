@extends('layouts.user')
@section('title','Tất cả sản phẩm')

@section('content')
@php
$favIds = auth()->check()
? auth()->user()->favorites()->pluck('products.id')->all()
: [];
@endphp

<section class="py-4">
  <div class="container">
    <div class="page-header">
      <div class="page-nav">
        <a class="btn continue-shopping" href="{{ route('home') }}">
          <i class="bi bi-arrow-left"></i>
          Quay lại trang chủ
        </a>
      </div>
      <h1 class="pageTitle">TẤT CẢ SẢN PHẨM</h1>
    </div>

    {{-- BỘ LỌC SẢN PHẨM --}}
    <div class="product-filters mb-4">
      <form
        method="GET"
        action="{{ route('product.list') }}"
        class="row g-3 align-items-end"
        id="product-filter-form" data-no-loading>
    
        {{-- THỂ LOẠI --}}
        <div class="col-lg-2 col-md-6">
          <label class="form-label mb-1">Thể loại</label>
          <select name="category_id" class="form-select setupSelect2">
            <option value="">Tất cả</option>
            @foreach(($categories ?? []) as $category)
            <option
              value="{{ $category->id }}"
              @selected(request('category_id')==$category->id)>
              {{ $category->name }}
            </option>
            @endforeach
          </select>
        </div>

        {{-- TÁC GIẢ --}}
        <div class="col-lg-2 col-md-6">
          <label class="form-label mb-1">Tác giả</label>
          <select name="author_id" class="form-select setupSelect2">
            <option value="">Tất cả</option>
            @foreach(($authors ?? []) as $author)
            <option
              value="{{ $author->id }}"
              @selected(request('author_id')==$author->id)>
              {{ $author->name }}
            </option>
            @endforeach
          </select>
        </div>

        {{-- NHÀ XUẤT BẢN --}}
        <div class="col-lg-2 col-md-6">
          <label class="form-label mb-1">Nhà xuất bản</label>
          <select name="publisher_id" class="form-select setupSelect2">
            <option value="">Tất cả</option>
            @foreach(($publishers ?? []) as $publisher)
            <option
              value="{{ $publisher->id }}"
              @selected(request('publisher_id')==$publisher->id)>
              {{ $publisher->name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 col-md-6">
          <button type="submit" class="btn-submit-filter btn btn-primary w-100">
            <i class="bi bi-funnel me-1"></i> Lọc
          </button>
        </div>

        <div class="col-lg-4 col-md-12 text-md-end">
          <a href="{{ route('product.list') }}"
            class="btn-remove-filter btn btn-outline-secondary w-lg-auto">
            Xóa lọc
          </a>
        </div>

        @php
        $maxPrice = (int) ($maxPrice ?? 0);
        $priceMin = (int) request('price_min', 0);
        $priceMax = (int) request('price_max', $maxPrice);

        if ($maxPrice > 0 && $priceMax === 0) {
        $priceMax = $maxPrice;
        }
        if ($priceMax > $maxPrice) {
        $priceMax = $maxPrice;
        }
        if ($priceMin < 0) {
          $priceMin=0;
          }
          if ($priceMin> $priceMax) {
          $priceMin = $priceMax;
          }
          @endphp

          {{-- KHOẢNG GIÁ BÁN (2 nút trên 1 thanh) --}}
          <div class="col-6">
            <label class="form-label mb-2">Khoảng giá bán</label>
            <div class="price-range-wrapper">
              {{-- bubble hiển thị giá trên 2 nút --}}
              <div class="price-bubble" id="priceBubbleMin">
                {{ number_format($priceMin, 0, ',', '.') }}đ
              </div>
              <div class="price-bubble" id="priceBubbleMax">
                {{ number_format($priceMax, 0, ',', '.') }}đ
              </div>

              {{-- track tổng + đoạn được chọn --}}
              <div class="price-range-track"></div>
              <div class="price-range-selected" id="priceRangeSelected"></div>

              {{-- 2 input range chồng lên nhau --}}
              <input
                type="range"
                class="price-range-input"
                id="priceRangeMin"
                min="0"
                max="{{ $maxPrice }}"
                step="1000"
                value="{{ $priceMin }}"
                @if($maxPrice <=0) disabled @endif>

              <input
                type="range"
                class="price-range-input"
                id="priceRangeMax"
                min="0"
                max="{{ $maxPrice }}"
                step="1000"
                value="{{ $priceMax }}"
                @if($maxPrice <=0) disabled @endif>

              {{-- hidden để backend xài filter --}}
              <input type="hidden" name="price_min" value="{{ $priceMin }}">
              <input type="hidden" name="price_max" value="{{ $priceMax }}">
            </div>
          </div>
      </form>
    </div>
    {{-- HẾT BỘ LỌC --}}

    {{-- BẮT ĐẦU: THANH SẮP XẾP --}}
    <div class="d-flex justify-content-end mb-3">
      <div class="col-lg-3 col-md-6">
        <label for="sort_by" class="form-label mb-1">Sắp xếp theo</label>
        <select class="form-select" id="sort_by" name="sort_by">
          <option value="latest" @selected(request('sort_by')=='latest' || !request('sort_by'))>Mới nhất</option>
          <option value="price_asc" @selected(request('sort_by')=='price_asc' )>Giá: Tăng dần</option>
          <option value="price_desc" @selected(request('sort_by')=='price_desc' )>Giá: Giảm dần</option>
          <option value="title_asc" @selected(request('sort_by')=='title_asc' )>Tên: A-Z</option>
          <option value="title_desc" @selected(request('sort_by')=='title_desc' )>Tên: Z-A</option>
        </select>
      </div>
    </div>
    {{-- KẾT THÚC: THANH SẮP XẾP --}}


    @if($products->isEmpty())
    <div
      class="text-center py-5"
      id="product-list-wrapper"
      data-list-url="{{ route('product.list') }}">
      <h5 class="mb-3">Chưa có sản phẩm</h5>
      <a href="{{ route('home') }}" class="btn btn-primary">Về trang chủ</a>
    </div>
    @else
    {{-- BẮT ĐẦU: WRAPPER CHO SẢN PHẨM VÀ PHÂN TRANG (CHO AJAX) --}}
    <div id="product-list-wrapper">
      <div class="row g-4" id="booksContainer">
        @forelse($products as $product)
        @php $isFav = in_array($product->id, $favIds, true); @endphp
        <div class="col-lg-4 col-md-6">
          <div class="card book-card h-100">
            <div class="book-cover">
              <img
                src="{{ asset('storage/products/'.$product->image) }}"
                alt="{{ $product->title }}"
                class="book-cover-img" {{-- <-- PHẢI CÓ CLASS NÀY --}}
                loading="lazy">
            </div>

            <div class="card-body d-flex flex-column">
              <h6 class="card-title line-clamp-2 mb-1">
                <a
                  href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}"
                  class="text-body text-decoration-none">
                  {{ $product->title }}
                </a>
              </h6>

              @php
              $authorNames = optional($product->authors)->pluck('name')->join(', ');
              @endphp
              <p class="card-text text-muted mb-3">{{ $authorNames ?: 'Không rõ tác giả' }}</p>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <span class="price">
                    {{ number_format($product->selling_price_vnd, 0, ',', '.') }} VNĐ
                  </span>
                  @if(!empty($product->listed_price_vnd) && $product->listed_price_vnd > $product->selling_price_vnd)
                  <small class="text-muted text-decoration-line-through ms-2">
                    {{ number_format($product->listed_price_vnd, 0, ',', '.') }} VNĐ
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
                  <span class="js-fav-label">{{ $isFav ? 'Bỏ thích' : 'Thích' }}</span>
                </button>
              </div>

              <div class="mt-auto d-grid gap-2">
                <a href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}" class="btn btn-outline-primary">
                  <i class="fas fa-eye me-2"></i>Xem chi tiết
                </a>

                <form
                  action="{{ route('cart.item.add') }}"
                  method="POST"
                  class="flex-fill d-flex add-to-cart-form"
                  data-no-loading>
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <input type="hidden" name="qty" value="1">
                  <button
                    type="submit"
                    class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-cart-plus me-2" aria-hidden="true"></i>Thêm vào giỏ
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

      <div class="mt-3" id="pagination-links">
        {{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
      </div>
    </div>
    {{-- KẾT THÚC: WRAPPER --}}
    @endif
  </div>
</section>
@endsection