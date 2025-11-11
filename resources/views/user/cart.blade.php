@extends('layouts.user')
@section('title','Giỏ hàng')

@section('content')
<div class="container my-4 py-2 cart-page">
  <div class="page-header">
    <h1 class="pageTitle"><i class="bi bi-cart-fill"></i> GIỎ HÀNG CỦA BẠN </h1>
    <div class="page-nav">
      <a class="btn continue-shopping" href="{{ route('home') }}">
        <i class="bi bi-arrow-left"></i>
        Quay lại trang chủ
      </a>
    </div>
  </div>

  @if(!empty($cart['warnings']))
  <div class="alert alert-warning">
    @foreach($cart['warnings'] as $w)
    <div>{{ $w }}</div>
    @endforeach
  </div>
  @endif

  <div class="row g-3">
    <!-- LEFT: Items -->
    <div class="col-lg-8">
      <div class="card items-section">
        <div class="select-all-section d-flex align-items-center gap-2 mb-3">
          <input type="checkbox" id="selectAll" class="form-check-input select-all-checkbox">
          <label for="selectAll" class="m-0 fw-semibold select-all-label">Chọn tất cả sản phẩm</label>
        </div>

        @if(empty($cart['items']))
        <div class="text-center text-muted py-5 empty-state">
          <i class="bi bi-cart-x" style="font-size:64px;"></i>
          <h3 class="mt-2">Giỏ hàng trống</h3>
          <p>Hãy thêm sản phẩm để tiếp tục mua sắm!</p>
        </div>
        @else
        <div id="cartItems">
          @foreach($cart['items'] as $line)
          @php
          $img = isset($line['image']) ? asset('storage/products/'.$line['image']) : asset('images/placeholder-120x160.svg');
          @endphp
          <div
            class="cart-item item card p-3 mb-3"
            data-key="{{ $line['key'] }}"
            data-price="{{ (int)$line['price'] }}"
            data-qty="{{ (int)$line['qty'] }}"
            data-total="{{ (int)$line['line_total'] }}">
            <div class="d-flex gap-3 align-items-start">
              <div class="pt-1">
                <input type="checkbox" class="item-checkbox form-check-input">
              </div>
              <img src="{{ $img }}" alt="{{ $line['title'] }}" class="rounded shadow-sm book-image">
              <div class="flex-grow-1 item-details">
                <div class="d-flex justify-content-between">
                  <div class="fw-semibold book-title">{{ $line['title'] }}</div>
                  <div class="text-primary fw-semibold book-price">{{ number_format($line['price'],0,',','.') }}đ</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 item-actions">
                  <div class="d-flex align-items-center gap-2 quantity-section">
                    <span class="text-muted small quantity-label">Số lượng:</span>

                    {{-- Giảm --}}
                    <form action="{{ route('cart.item.update', $line['key']) }}" method="POST" class="d-inline" data-no-loading>
                      @csrf @method('PATCH')
                      <input type="hidden" name="qty" value="{{ max(1, (int)$line['qty'] - 1) }}">
                      <button type="submit" class="btn btn-sm btn-outline-secondary quantity-btn" {{ $line['qty']<=1 ? 'disabled' : '' }}>
                        <i class="bi bi-dash"></i>
                      </button>
                    </form>

                    <span class="fw-semibold quantity-display">{{ (int)$line['qty'] }}</span>

                    {{-- Tăng --}}
                    <form action="{{ route('cart.item.update', $line['key']) }}" method="POST" class="d-inline" data-no-loading>
                      @csrf @method('PATCH')
                      <input type="hidden" name="qty" value="{{ (int)$line['qty'] + 1 }}">
                      <button type="submit" class="btn btn-sm btn-outline-secondary quantity-btn">
                        <i class="bi bi-plus"></i>
                      </button>
                    </form>
                  </div>

                  <div class="d-flex align-items-center gap-3">
                    <div class="line-total text-primary fw-bold item-total">{{ number_format($line['line_total'],0,',','.') }}đ</div>
                    <form action="{{ route('cart.item.remove', $line['key']) }}" method="POST" class="d-inline remove-cart-item-form" data-no-loading>
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-link text-danger p-0 remove-btn" title="Xoá">
                        <i class="bi bi-trash" style="font-size:1.25rem;"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>

    <!-- RIGHT: Summary -->
    <div class="col-lg-4">
      <div class="card summary-section">
        <div class="summary-title h5 mb-2">Tóm tắt đơn hàng</div>
        <div id="selectedCount" class="selected-count">Đã chọn 0 sản phẩm</div>

        <div class="d-flex justify-content-between mb-2 summary-row">
          <span>Tạm tính</span><span id="subtotal">0₫</span>
        </div>
        <div class="d-flex justify-content-between mb-2 summary-row">
          <span>Phí vận chuyển</span><span id="shipping">0₫</span>
        </div>
        <div class="d-flex justify-content-between border-top pt-2 mt-2 summary-row total">
          <span class="fw-semibold">Tổng cộng</span><span id="total" class="fw-bold text-primary amount">0₫</span>
        </div>

        <form action="{{ route('checkout.index') }}" method="GET" id="goCheckoutForm" class="mt-3">
          <input type="hidden" name="keys" id="selectedKeys">
          <button type="submit" class="btn btn-primary w-100 checkout-btn" id="proceedCheckout" disabled>
            <i class="bi bi-credit-card"></i> Tiến hành thanh toán
          </button>
        </form>

        <a class="btn continue-shopping mt-2" href="{{ route('home') }}">
          <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/pages/cart-page.js'])
@endpush