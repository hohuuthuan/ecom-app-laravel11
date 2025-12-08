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
    @endphp

    <div class="col-lg-4 col-md-6">
      <div class="card book-card h-100">
        <div class="book-cover">
          <img
            src="{{ asset('storage/products/'.$product->image) }}"
            alt="{{ $product->title }}"
            class="book-cover-img"
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
          <p class="card-text text-muted mb-3">{{ $authorNames ?: 'Không rõ tác giả' }}</p>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <span class="price">
                {{ number_format((int)$product->selling_price_vnd, 0, ',', '.') }} VNĐ
              </span>
              @if(!empty($product->listed_price_vnd) && (int)$product->listed_price_vnd > (int)$product->selling_price_vnd)
                <small class="text-muted text-decoration-line-through ms-2">
                  {{ number_format((int)$product->listed_price_vnd, 0, ',', '.') }} VNĐ
                </small>
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
            <a
              href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}"
              class="btn btn-outline-primary">
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
