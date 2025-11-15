@extends('layouts.user')

@section('title', 'Cảm ơn bạn đã đặt hàng')

@section('content')
<div class="thanks-page">
  <div class="container py-4">
    <section class="thank-you-section">
      <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
          <div class="thank-you-card">
            {{-- icons nền --}}
            <i class="bi bi-book book-icons book-icon-1"></i>
            <i class="bi bi-journal-bookmark-fill book-icons book-icon-2"></i>

            {{-- icon thành công --}}
            <div class="success-icon">
              <i class="bi bi-check-lg"></i>
            </div>

            <h1 class="main-title" id="mainTitle">Cảm ơn bạn đã đặt hàng!</h1>
            <p class="subtitle" id="subtitle">Đơn hàng của bạn đã được ghi nhận</p>

            @php
              /** @var \App\Models\Order|null $order */
              $orderCode = isset($order) ? $order->code : null;
              $rawStatus = isset($order) ? (string) $order->status : 'pending';

              $statusLabel = 'Đang xử lý';
              $statusClass = 'text-warning fw-bold';

              if ($rawStatus === 'confirmed') {
                  $statusLabel = 'Đã xác nhận';
                  $statusClass = 'text-success fw-bold';
              } elseif ($rawStatus === 'pending') {
                  $statusLabel = 'Chờ xác nhận';
                  $statusClass = 'text-warning fw-bold';
              } elseif ($rawStatus === 'cancelled') {
                  $statusLabel = 'Đã hủy';
                  $statusClass = 'text-danger fw-bold';
              } elseif ($rawStatus === 'shipped') {
                  $statusLabel = 'Đang giao';
                  $statusClass = 'text-info fw-bold';
              } elseif ($rawStatus === 'delivered') {
                  $statusLabel = 'Đã giao';
                  $statusClass = 'text-success fw-bold';
              }
            @endphp

            {{-- thông tin đơn hàng --}}
            <div class="order-info">
              <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                  <strong>Mã đơn hàng:</strong>
                  <div class="order-number" id="orderNumber">
                    @if($orderCode)
                      #{{ $orderCode }}
                    @else
                      #ĐANG_CẬP_NHẬT
                    @endif
                  </div>
                </div>
                <div class="col-md-6">
                  <strong>Trạng thái:</strong>
                  <div class="{{ $statusClass }}">
                    {{ $statusLabel }}
                  </div>
                </div>
              </div>
            </div>

            <p class="description" id="description">
              Chúng tôi sẽ giao sách đến bạn trong thời gian sớm nhất. 
              Bạn sẽ nhận được email xác nhận và thông tin theo dõi đơn hàng 
              (nếu đã cung cấp địa chỉ email).
            </p>

            <div class="features-list">
              <div class="feature-item">
                <i class="bi bi-truck"></i>
                <span>Giao hàng nhanh chóng trong 2–3 ngày làm việc</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-shield-check"></i>
                <span>Đóng gói cẩn thận, bảo vệ sách tốt nhất</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-envelope-fill"></i>
                <span>Thông báo tình trạng đơn hàng qua email</span>
              </div>
            </div>

            <a href="{{ route('home') }}" class="continue-btn" id="continueBtn">
              <i class="bi bi-arrow-left me-2"></i>
              <span id="buttonText">Tiếp tục mua sắm</span>
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
