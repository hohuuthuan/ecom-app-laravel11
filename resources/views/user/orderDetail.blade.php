@extends('layouts.user')
@section('title','Chi tiết đơn hàng')

@section('content')
<div class="container my-4 py-2 checkout-page">
  <div class="page-header">
    <div class="page-nav">
      <a
        class="btn continue-shopping"
        href="{{ route('user.profile.index', ['tab' => 'orders']) }}">
        <i class="bi bi-arrow-left"></i>
        Quay lại lịch sử đơn hàng
      </a>
    </div>
    <h1 class="pageTitle">
      <i class="bi bi-receipt-cutoff"></i>
      CHI TIẾT ĐƠN HÀNG
    </h1>
  </div>

  <div id="orderDetailPage">
    <div class="page-content">
      <div>
        {{-- THÔNG TIN ĐƠN HÀNG / NGƯỜI NHẬN --}}
        <div class="checkout-form">
          <div class="form-section">
            <h3>
              <i class="icon-info-person bi bi-person-fill"></i>
              Thông tin đơn hàng
            </h3>

            <div class="row checkout-inline-row">
              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Mã đơn:</span>
                  <span class="checkout-inline-value">{{ $order->code }}</span>
                </div>
              </div>

              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Ngày đặt:</span>
                  <span class="checkout-inline-value">
                    {{ optional($order->placed_at)->format('d/m/Y H:i') }}
                  </span>
                </div>
              </div>
            </div>

            <div class="row checkout-inline-row mt-2">
              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Họ và tên:</span>
                  <span class="checkout-inline-value">
                    {{ $order->receiver_name ?? optional($order->user)->name ?? 'Không rõ' }}
                  </span>
                </div>
              </div>

              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Số điện thoại:</span>
                  <span class="checkout-inline-value">
                    {{ $order->receiver_phone ?? optional($order->user)->phone ?? 'Không rõ' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="row checkout-inline-row mt-2">
              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Email:</span>
                  <span class="checkout-inline-value">
                    {{ $order->email ?? optional($order->user)->email ?? 'Không có' }}
                  </span>
                </div>
              </div>

              <div class="col-md-6">
                @php
                $statusRaw = strtoupper((string) $order->status);
                $statusLabel = 'Không xác định';
                $badgeClass = 'badge-status-pending';

                if (in_array($statusRaw, ['PENDING', 'PROCESSING', 'PICKING', 'SHIPPING'], true)) {
                  $statusLabel = match ($statusRaw) {
                    'PENDING'    => 'Chờ xử lý',
                    'PROCESSING' => 'Tiếp nhận đơn, chuyển đơn sang đơn vị kho',
                    'PICKING'    => 'Đang chuẩn bị hàng',
                    'SHIPPING'   => 'Đã giao cho đơn vị vận chuyển',
                    default      => 'Đang xử lý',
                  };
                  $badgeClass = 'badge-status-pending';
                } elseif (in_array($statusRaw, ['COMPLETED', 'DELIVERED'], true)) {
                  $statusLabel = 'Hoàn tất đơn hàng';
                  $badgeClass = 'badge-status-success';
                } elseif (in_array($statusRaw, ['CANCELLED', 'DELIVERY_FAILED', 'RETURNED'], true)) {
                  $statusLabel = match ($statusRaw) {
                    'CANCELLED'       => 'Đã hủy đơn',
                    'DELIVERY_FAILED' => 'Giao hàng thất bại',
                    'RETURNED'        => 'Hoàn / trả hàng',
                    default           => 'Đã hủy / giao thất bại',
                  };
                  $badgeClass = 'badge-status-cancel';
                }
                @endphp
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Trạng thái:</span>
                  <span class="checkout-inline-value">
                    <span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span>
                  </span>
                </div>
              </div>
            </div>

            {{-- ĐỊA CHỈ GIAO HÀNG --}}
            @if($order->shipment)
            <div class="row checkout-inline-row mt-2">
              <div class="col-12">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Địa chỉ giao hàng:</span>
                  <span class="checkout-inline-value">
                    {{ $order->shipment->address }}
                    @if($order->shipment->ward || $order->shipment->province)
                      , {{ optional($order->shipment->ward)->name }},
                      {{ optional($order->shipment->province)->name }}
                    @endif
                    @if(!empty($order->shipment->note))
                      <br>
                      <small class="text-muted">Ghi chú: {{ $order->shipment->note }}</small>
                    @endif
                  </span>
                </div>
              </div>
            </div>
            @endif

            @if($order->discount)
            <div class="row checkout-inline-row mt-2">
              <div class="col-md-6">
                <div class="checkout-inline-item">
                  <span class="checkout-inline-label">Mã giảm giá:</span>
                  <span class="checkout-inline-value">
                    {{ $order->discount->code }}
                  </span>
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>

        {{-- DANH SÁCH SẢN PHẨM --}}
        <div class="checkout-items">
          <h3 class="checkout-items-title">
            <i class="icon-selected-product bi bi-bag-check-fill"></i>
            Sản phẩm trong đơn
          </h3>

          <div id="orderItemsList">
            @forelse($order->items as $item)
              @php
              $product = $item->product;
              $imgPath = $product && $product->image
                ? asset('storage/products/' . $product->image)
                : asset('images/placeholder-120x160.svg');

              $qty = (int) ($item->quantity ?? 0);
              $unitPrice = (int) ($item->unit_price_vnd ?? $item->unit_price ?? 0);
              $lineTotal = (int) ($item->line_total_vnd ?? $item->total_price_vnd ?? ($qty * $unitPrice));
              @endphp

              <div class="checkout-item">
                <img
                  src="{{ $imgPath }}"
                  alt="{{ $product->title ?? 'Sản phẩm' }}"
                  class="checkout-item-image">
                <div class="checkout-item-details">
                  <div class="checkout-item-title">
                    @if($product)
                      <a
                        href="{{ route('product.detail', ['slug' => $product->slug, 'id' => $product->id]) }}"
                        class="text-decoration-none text-body">
                        {{ $product->title }}
                      </a>
                    @else
                      {{ $item->product_name ?? 'Sản phẩm đã bị xóa' }}
                    @endif
                  </div>
                  <div class="checkout-item-quantity">
                    Số lượng: {{ $qty }}
                    × {{ number_format($unitPrice, 0, ',', '.') }}VNĐ
                  </div>
                </div>
                <div class="checkout-item-price">
                  {{ number_format($lineTotal, 0, ',', '.') }}VNĐ
                </div>
              </div>
            @empty
              <p class="text-muted mb-0">Đơn hàng này không có sản phẩm nào.</p>
            @endforelse
          </div>
        </div>
      </div>

      {{-- TÓM TẮT THANH TOÁN --}}
      <div class="summary-section">
        <div class="summary-title">
          Chi tiết thanh toán
        </div>

        <div class="summary-row">
          <span>Tạm tính:</span>
          <span>
            {{ number_format($subtotal, 0, ',', '.') }}VNĐ
          </span>
        </div>

        <div class="summary-row">
          <span>Phí vận chuyển:</span>
          <span>
            {{ number_format($shipping, 0, ',', '.') }}VNĐ
          </span>
        </div>

        <div class="summary-row">
          <span>Giảm giá:</span>
          <span class="checkout-discount">
            -{{ number_format($discountAmount, 0, ',', '.') }}VNĐ
          </span>
        </div>

        <div class="summary-row total">
          <span>Tổng tiền:</span>
          <span class="amount">
            {{ number_format($grandTotal, 0, ',', '.') }}VNĐ
          </span>
        </div>

        <div class="payment-method-section mt-3">
          <div class="payment-method-title">
            Phương thức thanh toán
          </div>
          <div class="payment-option selected">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <div class="payment-option-name">
                  @php
                  $method = $order->payment_method ?? 'cod';
                  $methodLabel = 'COD - Thanh toán khi nhận hàng';

                  if ($method === 'momo') {
                    $methodLabel = 'Ví MoMo';
                  } elseif ($method === 'vnpay') {
                    $methodLabel = 'VNPAY';
                  }

                  $canReorder = in_array(strtoupper((string) $order->status), ['DELIVERED', 'COMPLETED'], true);
                  @endphp
                  {{ $methodLabel }}
                </div>
                <div class="payment-option-desc">
                  @if($order->payment_status === 'paid')
                    Trạng thái thanh toán: Đã thanh toán
                  @else
                    Trạng thái thanh toán: Chưa thanh toán
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        @if($canReorder)
          <form
            method="POST"
            action="{{ route('user.profile.orders.reorder', $order->id) }}"
            class="d-inline">
            @csrf
            <button
              type="submit"
              class="checkout-btn mt-2 d-inline-flex align-items-center justify-content-center">
              <i class="bi bi-book"></i>
              <span class="ms-1">Mua lại đơn này</span>
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
