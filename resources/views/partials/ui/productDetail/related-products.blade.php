@php
  /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $products */

  $favIds = auth()->check()
    ? auth()->user()->favorites()->pluck('products.id')->all()
    : [];
@endphp

<div class="row g-4">
  @forelse($products as $product)
    @php
      $isFav = auth()->check()
        ? in_array($product->id, $favIds, true)
        : false;

      $authorNames = optional($product->authors)->pluck('name')->join(', ');

      $discountPercent = (int) ($product->discount_percent ?? 0);
      $discountPercent = max(0, min(100, $discountPercent));

      $sellingPrice = (int) ($product->selling_price_vnd ?? 0);
      $finalPrice = $discountPercent > 0
        ? (int) round($sellingPrice * (100 - $discountPercent) / 100)
        : $sellingPrice;

      $hasPercentDiscount = $discountPercent > 0 && $finalPrice > 0 && $finalPrice < $sellingPrice;

      $detailUrl = route('product.detail', ['slug' => $product->slug, 'id' => $product->id]);
    @endphp

    <div class="col-lg-4 col-md-6">
      <div class="card book-card h-100">
        <div class="book-cover position-relative">
          <img
            src="{{ asset('storage/products/'.$product->image) }}"
            alt="{{ $product->title }}"
            class="book-cover-img"
            loading="lazy">

          @if($hasPercentDiscount)
            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
              -{{ $discountPercent }}%
            </span>
          @endif
        </div>

        <div class="card-body d-flex flex-column">
          <h6 class="card-title line-clamp-2 mb-1">
            <a
              href="{{ $detailUrl }}"
              class="text-body text-decoration-none">
              {{ $product->title }}
            </a>
          </h6>

          <p class="card-text text-muted mb-3">{{ $authorNames ?: 'Không rõ tác giả' }}</p>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              @if($hasPercentDiscount)
                <span class="price text-danger fw-bold">
                  {{ number_format($finalPrice, 0, ',', '.') }} VNĐ
                </span>
                <small class="text-muted text-decoration-line-through ms-2">
                  {{ number_format($sellingPrice, 0, ',', '.') }} VNĐ
                </small>
              @else
                <span class="price">
                  {{ number_format($sellingPrice, 0, ',', '.') }} VNĐ
                </span>
                @if(!empty($product->listed_price_vnd) && (int)$product->listed_price_vnd > $sellingPrice)
                  <small class="text-muted text-decoration-line-through ms-2">
                    {{ number_format((int)$product->listed_price_vnd, 0, ',', '.') }} VNĐ
                  </small>
                @endif
              @endif
            </div>

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
          </div>

          <div class="mt-auto d-grid gap-2">
            <a href="{{ $detailUrl }}" class="btn btn-outline-primary">
              <i class="fas fa-eye me-2"></i>Xem chi tiết
            </a>

            <form action="{{ route('cart.item.add') }}" method="post" class="add-to-cart-form" data-no-loading>
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
      <p class="text-muted mb-0">Chưa có sản phẩm liên quan.</p>
    </div>
  @endforelse
</div>

@if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
  <div class="mt-3 d-flex justify-content-center related-pagination">
    {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
  </div>
@endif
