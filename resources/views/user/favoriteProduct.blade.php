@extends('layouts.user')
@section('title','Sản phẩm yêu thích')

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
      <h1 class="pageTitle">SẢN PHẨM BẠN ĐÃ THÍCH</h1>
      <span class="badge bg-primary">{{ $products->count() }} sản phẩm</span>
    </div>

    @if($products->isEmpty())
    <div class="text-center py-5">
      <h5 class="mb-1">Chưa có sản phẩm yêu thích</h5>
      <p class="text-muted mb-3">Hãy quay lại trang chủ và thêm vài cuốn sách.</p>
      <a href="{{ route('home') }}" class="btn btn-primary">Về trang chủ</a>
    </div>
    @else
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
              <a
                href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}"
                class="text-body text-decoration-none">
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
                  <small class="text-muted text-decoration-line-through ms-2">
                    {{ number_format($originalPrice, 0, ',', '.') }} VNĐ
                  </small>
                @else
                  <span class="price">
                    {{ number_format($originalPrice, 0, ',', '.') }} VNĐ
                  </span>
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

              <form id="addToCartForm" action="{{ route('cart.item.add') }}" method="POST" class="flex-fill d-flex add-to-cart-form" data-no-loading>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="qty" id="addToCartQty" value="1">
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
    @endif
  </div>
</section>
@endsection
