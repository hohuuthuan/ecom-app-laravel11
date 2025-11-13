@extends('layouts.user')
@section('title','Thanh toán')

@section('content')
<div class="container my-4 py-2 checkout-page">
  <div class="page-header">
    <div class="page-nav">
      <a class="btn continue-shopping" href="{{ route('home') }}">
        <i class="bi bi-arrow-left"></i>
        Quay lại trang chủ
      </a>
    </div>
    <h1 class="pageTitle"><i class="bi bi-cart-fill"></i> THANH TOÁN </h1>
  </div>

  <div id="checkoutPage"
    data-subtotal="{{ (int)$subtotal }}"
    data-shipping="{{ (int)$shipping }}">
    <div class="page-content">
      <div>
        <div class="checkout-form">
          <div class="form-section">
            <h3><i class="bi bi-person-fill"></i> Thông tin giao hàng</h3>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="fullName">Họ và tên <span class="text-danger">*</span></label>
                  {{-- dùng form attr để liên kết input với form ở cột phải --}}
                  <input type="text" id="fullName" name="full_name" form="placeOrderForm"
                    class="form-input" placeholder="Nhập họ và tên" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="phone">Số điện thoại <span class="text-danger">*</span></label>
                  <input type="tel" id="phone" name="phone" form="placeOrderForm"
                    class="form-input" placeholder="Nhập số điện thoại" required>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="email">Email</label>
              <input type="email" id="email" name="email" form="placeOrderForm"
                class="form-input" placeholder="Nhập email">
            </div>

            <div class="form-group">
              <label class="form-label" for="address">Địa chỉ giao hàng <span class="text-danger">*</span></label>
              <input type="text" id="address" name="address" form="placeOrderForm"
                class="form-input" placeholder="Nhập địa chỉ chi tiết" required>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="city">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                  <input type="text" id="city" name="city" form="placeOrderForm"
                    class="form-input" placeholder="Chọn tỉnh/thành" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="district">Quận/Huyện <span class="text-danger">*</span></label>
                  <input type="text" id="district" name="district" form="placeOrderForm"
                    class="form-input" placeholder="Chọn quận/huyện" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="ward">Phường/Xã <span class="text-danger">*</span></label>
                  <input type="text" id="ward" name="ward" form="placeOrderForm"
                    class="form-input" placeholder="Chọn phường/xã" required>
                </div>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3><i class="bi bi-tag-fill"></i> Nhập mã giảm giá</h3>
            <div class="discount-section">
              <div class="discount-input-group">
                <input type="text" id="discountCode" class="form-input discount-input" placeholder="Nhập mã giảm giá">
                <button class="apply-btn" type="button" onclick="applyDiscount()">Áp Dụng</button>
              </div>
              <div id="discountMessage" style="margin-top: 10px; font-size: 14px;"></div>
            </div>
          </div>
        </div>

        <div class="checkout-items">
          <h3 style="color: #2d3748; font-weight: 600; margin-bottom: 20px;">
            <i class="bi bi-bag-check-fill"></i> Sản Phẩm Đã Chọn
          </h3>
          <div id="checkoutItemsList">
            @foreach($items as $line)
            @php
            $img = !empty($line['image'])
            ? asset('storage/products/'.$line['image'])
            : asset('images/placeholder-120x160.svg');
            @endphp
            <div class="checkout-item">
              <img src="{{ $img }}" alt="{{ $line['title'] }}" class="checkout-item-image">
              <div class="checkout-item-details">
                <div class="checkout-item-title">{{ $line['title'] }}</div>
                <div class="checkout-item-quantity">
                  Số lượng: {{ (int)$line['qty'] }} × {{ number_format($line['price'],0,',','.') }}₫
                </div>
              </div>
              <div class="checkout-item-price">{{ number_format($line['line_total'],0,',','.') }}₫</div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <form action="{{ route('checkout.place') }}" method="POST">
        @csrf
        <div class="summary-section">
          <div class="summary-title">
            Chi tiết thanh toán
          </div>
          <div class="summary-row"><span>Tạm tính:</span> <span id="checkoutSubtotal">{{ number_format($subtotal,0,',','.') }}₫</span>
          </div>
          <div class="summary-row"><span>Phí vận chuyển:</span> <span id="checkoutShipping">{{ number_format($shipping,0,',','.') }}₫</span>
          </div>
          <div class="summary-row"><span>Giảm giá:</span> <span id="checkoutDiscount" style="color: #48bb78;">-0₫</span>
          </div>
          <div class="summary-row total"><span>Tổng thanh toán:</span> <span class="amount" id="checkoutTotal">{{ number_format($subtotal + $shipping,0,',','.') }}₫</span>
          </div>
          <button type="submit" class="checkout-btn">
            <i class="bi bi-check-circle"></i> Đặt Hàng
          </button>
          <a href="{{ route('cart') }}">
            <button type="button" class="continue-shopping">
              <i class="bi bi-arrow-left"></i> Quay lại giỏ hàng
            </button>
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/pages/checkout-page.js'])
@endpush