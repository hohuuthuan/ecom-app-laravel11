@extends('layouts.user')
@section('title','Thanh toán')

@section('content')
<div class="container my-4 py-2 checkout-page">
  @php
  $walletDiscounts = $walletDiscounts ?? collect();
  @endphp

  <div class="page-header">
    <div class="page-nav">
      <a class="btn continue-shopping" href="{{ route('cart') }}">
        <i class="bi bi-arrow-left"></i>
        Quay lại giỏ hàng
      </a>
    </div>
    <h1 class="pageTitle">
      <i class="bi bi-cart-fill"></i> THANH TOÁN
    </h1>
  </div>

  <div
    id="checkoutPage"
    data-subtotal="{{ (int) $subtotal }}"
    data-shipping="{{ (int) $shipping }}">
    <div class="page-content">
      <div>
        {{-- THÔNG TIN NGƯỜI NHẬN --}}
        <div class="checkout-form">
          <div class="form-section">
            <h3>
              <i class="icon-info-person bi bi-person-fill"></i>
              Thông tin người nhận
            </h3>
            <div class="row checkout-inline-row">
              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Họ và tên:</span>
                  <span class="checkout-inline-value">{{ $fullName ?: 'Chưa có họ tên' }}</span>
                </div>
              </div>

              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Số điện thoại:</span>
                  <span class="checkout-inline-value">
                    {{ $phone ?: 'Chưa có số điện thoại' }}
                  </span>
                </div>
              </div>
            </div>

            {{-- ẨN: GỬI LÊN HỌ TÊN + SĐT --}}
            <input
              type="hidden"
              name="receiver_name"
              value="{{ $fullName }}"
              form="placeOrderForm">
            <input
              type="hidden"
              name="receiver_phone"
              value="{{ $phone }}"
              form="placeOrderForm">

            <div class="form-group mt-3">
              <label class="form-label" for="email">
                <span class="checkout-inline-label">Email:</span>
                <span class="text-muted">(không bắt buộc)</span>
              </label>
              <input
                type="email"
                id="email"
                name="email"
                form="placeOrderForm"
                class="form-input"
                placeholder="Nhập email"
                value="{{ $email }}">
            </div>
          </div>
          <hr>

          {{-- ĐỊA CHỈ GIAO HÀNG --}}
          <div class="form-section">
            <section class="profile-section active" data-section="checkout-address">
              <div class="address-header">
                <div class="address-header-text">
                  <h3>
                    <i class="icon-delivery-address bi bi-geo-alt-fill"></i>
                    Địa chỉ nhận hàng
                  </h3>
                  <div class="address-header-subtitle">
                    Đơn hàng sẽ được giao đến địa chỉ này
                  </div>
                </div>

                @if ($selectedAddress)
                <button
                  type="button"
                  class="address-add-btn btn btn-sm btn-outline-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#selectAddressModal">
                  <i class="bi bi-arrow-repeat me-1"></i>
                  Chọn lại địa chỉ
                </button>
                @endif
              </div>

              @if ($selectedAddress)
              <div class="address-card" id="selectedAddressCard">
                <div class="address-card-header">
                  <div class="address-card-title">
                    <i class="bi bi-geo-alt"></i>
                    <span class="selected-address-text">
                      {{ $selectedAddress->address }}
                    </span>
                  </div>
                </div>

                <div class="address-card-body" id="selectedAddressBody">
                  {{ $selectedAddress->address }}
                  @if ($selectedAddress->ward || $selectedAddress->province)
                  , {{ optional($selectedAddress->ward)->name }},
                  {{ optional($selectedAddress->province)->name }}
                  @endif

                  @if (!empty($selectedAddress->note))
                  <br>
                  <small class="text-muted">Ghi chú: {{ $selectedAddress->note }}</small>
                  @endif
                </div>
              </div>

              <input
                type="hidden"
                name="shipping_address_id"
                id="shippingAddressId"
                form="placeOrderForm"
                value="{{ $selectedAddress->id }}">
              @else
              <div class="address-card">
                <div class="address-card-body">
                  <p class="text-muted mb-2">
                    Bạn chưa có địa chỉ giao hàng nào. Hãy thêm địa chỉ mới để đặt hàng nhanh hơn.
                  </p>
                  <button
                    type="button"
                    class="address-add-btn btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addAddressModal">
                    <span>+</span>
                    <span>Thêm địa chỉ</span>
                  </button>
                </div>
              </div>
              @endif
            </section>
          </div>
        </div>

        {{-- DANH SÁCH SẢN PHẨM --}}
        <div class="checkout-items">
          <h3 class="checkout-items-title">
            <i class="icon-selected-product bi bi-bag-check-fill"></i> Sản Phẩm Đã Chọn
          </h3>
          <div id="checkoutItemsList">
            @foreach ($items as $line)
            @php
            $img = !empty($line['image'])
            ? asset('storage/products/' . $line['image'])
            : asset('images/placeholder-120x160.svg');
            @endphp
            <div class="checkout-item">
              <img
                src="{{ $img }}"
                alt="{{ $line['title'] }}"
                class="checkout-item-image">
              <div class="checkout-item-details">
                <div class="checkout-item-title">
                  {{ $line['title'] }}
                </div>
                <div class="checkout-item-quantity">
                  Số lượng: {{ (int) $line['qty'] }}
                  × {{ number_format($line['price'], 0, ',', '.') }}VNĐ
                </div>
              </div>
              <div class="checkout-item-price">
                {{ number_format($line['line_total'], 0, ',', '.') }}VNĐ
              </div>

              {{-- ẨN: GỬI CẶP product_id => quantity --}}
              <input
                type="hidden"
                name="items[{{ $line['id'] }}]"
                value="{{ (int) $line['qty'] }}"
                form="placeOrderForm">
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- FORM ĐẶT HÀNG (COD) --}}
      <form
        id="placeOrderForm"
        action="{{ route('checkout.placeOrder') }}"
        method="POST">
        @csrf
        <div class="summary-section">
          <div class="summary-title">
            Chi tiết thanh toán
          </div>

          <div class="summary-row">
            <span>Tạm tính:</span>
            <span id="checkoutSubtotal">
              {{ number_format($subtotal, 0, ',', '.') }}VNĐ
            </span>
          </div>

          <div class="summary-row">
            <span>Phí vận chuyển:</span>
            <span id="checkoutShipping">
              {{ number_format($shipping, 0, ',', '.') }}VNĐ
            </span>
          </div>

          <div class="summary-row">
            <span>Giảm giá:</span>
            <span id="checkoutDiscount" class="checkout-discount">
              -0VNĐ
            </span>
          </div>
          <div class="summary-row total">
            <span>Tổng tiền:</span>
            <span class="amount" id="checkoutTotal">
              {{ number_format($subtotal + $shipping, 0, ',', '.') }}VNĐ
            </span>
          </div>

          <div class="discount-summary-block">
            <div class="discount-input-group">
              <input
                type="text"
                id="discountCode"
                name="discount_code"
                form="placeOrderForm"
                class="form-input discount-input"
                placeholder="Nhập mã giảm giá"
                value="{{ old('discount_code') }}">
              <button
                id="discountApplyButton"
                class="apply-btn"
                type="button"
                onclick="applyDiscount()">
                <span class="apply-btn-label">Áp Dụng</span>
                <span class="apply-btn-spinner d-none">
                  <span class="apply-btn-spinner-dot"></span>
                </span>
                <span class="apply-btn-check d-none">
                  <i class="bi bi-patch-check-fill"></i>
                </span>
              </button>
            </div>

            <button
              type="button"
              class="open-modal-voucher-walet-btn w-100 mt-2"
              data-bs-toggle="modal"
              data-bs-target="#walletDiscountModal">
              <i class="bi bi-ticket-perforated me-1"></i>
              Chọn mã giảm giá từ ví
            </button>

            <div id="discountMessage" class="discount-message"></div>
          </div>

          {{-- PHƯƠNG THỨC THANH TOÁN --}}
          <div class="payment-method-section">
            <div class="payment-method-title">
              Phương thức thanh toán
            </div>

            {{-- COD --}}
            <div
              class="payment-option js-payment-option selected"
              data-method="cod">
              <div class="d-flex align-items-center">
                <div class="payment-icon payment-icon-cod me-3">
                  <img src="{{ asset('storage/payment/COD.webp') }}" alt="" class="w-100">
                </div>
                <div class="flex-grow-1">
                  <div class="payment-option-name">
                    COD
                  </div>
                  <div class="payment-option-desc">
                    Thanh toán khi nhận hàng
                  </div>
                </div>
                <div class="radio-custom checked">
                  <div class="radio-dot show"></div>
                </div>
              </div>
            </div>

            {{-- MOMO --}}
            <div
              class="payment-option js-payment-option"
              data-method="momo">
              <div class="d-flex align-items-center">
                <div class="payment-icon payment-icon-momo me-3">
                  <img src="{{ asset('storage/payment/MOMO.jpg') }}" alt="" class="w-100">
                </div>
                <div class="flex-grow-1">
                  <div class="payment-option-name">
                    Ví MoMo
                  </div>
                  <div class="payment-option-desc">
                    Thanh toán qua ví điện tử MoMo
                  </div>
                </div>
                <div class="radio-custom">
                  <div class="radio-dot"></div>
                </div>
              </div>
            </div>

            {{-- VNPAY --}}
            <div
              class="payment-option js-payment-option"
              data-method="vnpay">
              <div class="d-flex align-items-center">
                <div class="payment-icon payment-icon-vnpay me-3">
                  <img src="{{ asset('storage/payment/VNPAY.png') }}" alt="" class="w-100">
                </div>
                <div class="flex-grow-1">
                  <div class="payment-option-name">
                    VNPAY
                  </div>
                  <div class="payment-option-desc">
                    Thanh toán qua cổng VNPAY
                  </div>
                </div>
                <div class="radio-custom">
                  <div class="radio-dot"></div>
                </div>
              </div>
            </div>

            <input
              type="hidden"
              name="payment_method"
              id="paymentMethodInput"
              value="cod">
          </div>

          <button
            type="submit"
            class="checkout-btn"
            id="paymentSubmitButton">
            <i class="bi bi-truck"></i>
            Đặt hàng
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL: DANH SÁCH MÃ GIẢM GIÁ TRONG VÍ --}}
<div class="modal fade" id="walletDiscountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">DANH SÁCH MÃ GIẢM GIÁ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body">
        <div id="walletCouponList">
          @if (($walletDiscounts ?? collect())->count() === 0)
          <div class="wallet-empty-state">
            <i class="bi bi-ticket-perforated"></i>
            <p>Không có mã giảm giá nào</p>
          </div>
          @else
          @foreach ($walletDiscounts as $walletItem)
          @php
          $d = $walletItem->discount;
          $type = strtoupper((string) ($d?->type ?? ''));
          $value = (int) ($d?->value ?? 0);
          $minOrder = (int) ($d?->min_order_value_vnd ?? 0);
          $isShip = $type === 'SHIPPING';
          $typeName = $isShip ? 'Giảm Phí Ship' : 'Giảm Giá Đơn Hàng';

          $desc = $isShip
          ? ('Giảm phí ship ' . number_format($value, 0, ',', '.') . 'VNĐ')
          : (
          $type === 'PERCENT'
          ? ('Giảm ' . $value . '%')
          : ('Giảm ' . number_format($value, 0, ',', '.') . 'VNĐ')
          );
          @endphp

          @if ($d)
          <div class="wallet-coupon-card js-wallet-discount-card" data-code="{{ $d->code }}">
            <div class="wallet-coupon-row">
              {{-- CỘT TRÁI --}}
              <div class="wallet-coupon-left">
                <div class="wallet-coupon-header">
                  <span class="wallet-coupon-code">{{ $d->code }}</span>
                </div>

                <div class="wallet-coupon-details">
                  <i class="bi bi-info-circle"></i>
                  <span>{{ $desc }}</span>
                </div>

                <div class="wallet-coupon-details mt-2">
                  <i class="bi bi-cart"></i>
                  <span>
                    Đơn tối thiểu:
                    <span class="wallet-min-order">
                      {{ number_format($minOrder, 0, ',', '.') }}VNĐ
                    </span>
                  </span>
                </div>
              </div>

              {{-- CỘT PHẢI --}}
              <div class="wallet-coupon-right">
                <div class="wallet-right-top">
                  <span class="wallet-coupon-type {{ $isShip ? 'type-ship' : 'type-order' }}">
                    <i class="bi {{ $isShip ? 'bi-truck' : 'bi-tag' }}"></i>
                    {{ $typeName }}
                  </span>
                </div>

                <div class="wallet-right-bottom">
                  <div class="wallet-coupon-actions">
                    <button
                      type="button"
                      class="btn btn-sm btn-primary js-wallet-discount-apply"
                      data-code="{{ $d->code }}">
                      Áp dụng
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif

          @endforeach
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@include('partials.ui.checkout.select-address-modal')
@include('partials.ui.profileOverview.add-address-modal')
@endsection

@push('scripts')
@vite(['resources/js/pages/checkout-page.js'])
@if ($errors->addressStore->any())
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('addAddressModal');
    if (!el || !window.bootstrap) return;
    var modal = window.bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
  });
</script>
@endif
@endpush
