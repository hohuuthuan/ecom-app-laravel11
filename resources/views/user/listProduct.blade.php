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
          <label class="form-label mb-1">Có giảm giá</label>
          <div class="d-flex align-items-center">
            <label class="discount-switch mb-0" for="discount_only" aria-label="Lọc sản phẩm có giảm giá">
              <input
                type="checkbox"
                id="discount_only"
                name="discount_only"
                value="1"
                class="discount-switch-input"
                @checked((int) request('discount_only')===1)>
              <span class="discount-switch-slider"></span>
            </label>
          </div>
        </div>

        <div class="col-lg-2 col-md-6">
          <button type="submit" class="btn-submit-filter btn btn-primary w-100">
            <i class="bi bi-funnel me-1"></i> Lọc
          </button>
        </div>

        <div class="col-lg-2 col-md-12">
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
          @php
          $maxPrice = (int) ($maxPrice ?? 0);

          $pricePresets = [
          ['label' => 'Tất cả', 'min' => 0, 'max' => $maxPrice],
          ['label' => '≤ 50k', 'min' => 0, 'max' => 50000],
          ['label' => '50k - 100k', 'min' => 50000, 'max' => 100000],
          ['label' => '100k - 200k', 'min' => 100000, 'max' => 200000],
          ['label' => '200k - 500k', 'min' => 200000, 'max' => 500000],
          ['label' => '≥ 500k', 'min' => 500000, 'max' => $maxPrice],
          ];

          $pricePresets = array_values(array_filter($pricePresets, function ($p) use ($maxPrice) {
          if ($maxPrice <= 0) return false;
            if ((int)$p['min']> $maxPrice) return false;
            if ((int)$p['max'] <= 0) return false;
              return true;
              }));
              @endphp

              <div class="price-preset-wrap mt-2">
              @foreach($pricePresets as $preset)
              @php
              $pMin = (int) $preset['min'];
              $pMax = (int) $preset['max'];
              if ($pMax > $maxPrice) $pMax = $maxPrice;
              $isActivePreset = ($pMin === (int) request('price_min', 0)) && ($pMax === (int) request('price_max', $maxPrice));
              @endphp

              <button
                type="button"
                class="btn btn-sm price-preset-btn {{ $isActivePreset ? 'is-active' : '' }}"
                data-price-min="{{ $pMin }}"
                data-price-max="{{ $pMax }}">
                {{ $preset['label'] }}
              </button>
              @endforeach
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
  <div id="product-list-wrapper">
    <div class="row g-4" id="booksContainer">
      @forelse($products as $product)
      @php
      $isFav = in_array($product->id, $favIds, true);

      $authorNames = optional($product->authors)->pluck('name')->join(', ');

      $discountPercent = (int) ($product->discount_percent ?? 0);
      $discountPercent = max(0, min(100, $discountPercent));

      $originalPrice = (int) ($product->selling_price_vnd ?? 0);

      $finalPrice = $discountPercent > 0
      ? (int) round($originalPrice * (100 - $discountPercent) / 100)
      : $originalPrice;

      $hasDiscount = $discountPercent > 0 && $finalPrice < $originalPrice;

        $detailUrl=route('product.detail', ['slug'=> $product->slug, 'id' => $product->id]);
        @endphp

        <div class="col-lg-4 col-md-6">
          <div class="card book-card h-100">
            <div class="book-cover position-relative">
              <img
                src="{{ asset('storage/products/'.$product->image) }}"
                alt="{{ $product->title }}"
                class="book-cover-img"
                loading="lazy">

              @if($hasDiscount)
              <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                -{{ $discountPercent }}%
              </span>
              @endif
            </div>

            <div class="card-body d-flex flex-column">
              <h6 class="card-title line-clamp-2 mb-1">
                <a href="{{ $detailUrl }}" class="text-body text-decoration-none">
                  {{ $product->title }}
                </a>
              </h6>

              <p class="card-text text-muted mb-3">{{ $authorNames ?: 'Không rõ tác giả' }}</p>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  @if($hasDiscount)
                  <span class="price text-danger fw-bold">
                    {{ number_format($finalPrice, 0, ',', '.') }} VNĐ
                  </span>
                  <small class="text-muted text-decoration-line-through ms-2" title="Giá gốc">
                    {{ number_format($originalPrice, 0, ',', '.') }} VNĐ
                  </small>
                  @else
                  <span class="price">
                    {{ number_format($originalPrice, 0, ',', '.') }} VNĐ
                  </span>
                  @endif
                </div>

                <button
                  type="button"
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
                <a href="{{ $detailUrl }}" class="btn btn-outline-primary">
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
                  <button type="submit" class="btn btn-primary btn-lg w-100">
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

    <div class="d-flex mt-3 list-product justify-content-center" id="pagination-links">
      {{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
    </div>
  </div>

  @endif
  </div>
</section>
@endsection

@push('scripts')
@vite(['resources/js/pages/product-list.js'])
@endpush
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('product-filter-form');
    var toggle = document.getElementById('discount_only');
    if (!form || !toggle) {
      return;
    }

    toggle.addEventListener('change', function() {
      form.submit();
    });
  });
</script>
@endpush