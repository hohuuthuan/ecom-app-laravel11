@extends('layouts.user')

@section('title','Ví vouchers')

@section('head')
  @parent
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @vite(['resources/css/voucher-center.css'])
@endsection

@section('content')
@php
  $now = now();

  $fmtVnd = static function ($v) {
    return number_format((int) ($v ?? 0), 0, ',', '.') . 'đ';
  };

  $fmtShortMoney = static function ($v) use ($fmtVnd) {
    $n = (int) ($v ?? 0);
    if ($n >= 1000 && $n % 1000 === 0 && $n < 1000000) return (string) ($n / 1000) . 'K';
    if ($n >= 1000000 && $n % 1000000 === 0) return (string) ($n / 1000000) . 'M';
    return $fmtVnd($n);
  };

  $fmtDate = static function ($v) {
    if (empty($v)) return 'Không giới hạn';
    try { return \Carbon\Carbon::parse($v)->format('d/m/Y'); } catch (\Throwable $e) { return (string) $v; }
  };
@endphp

<div class="container my-4 py-2" data-remove-url="{{ route('vouchers.remove') }}">
  <div class="page-header">
    <div class="page-nav">
      <a class="btn continue-shopping" href="{{ route('home') }}">
        <i class="bi bi-arrow-left"></i>
        Quay lại trang chủ
      </a>
    </div>
    <h1 class="pageTitle"><i class="bi bi-cart-fill"></i> VÍ VOUCHER </h1>
  </div>

  <div class="voucher-grid">
    @forelse ($discounts as $d)
      @php
        $id = (string) $d->id;
        $type = strtoupper((string) ($d->type ?? ''));
        $isShip = in_array($type, ['SHIP', 'SHIPPING'], true);
        $code = strtoupper((string) ($d->code ?? ''));

        $userUsed = (int) ($userUsedMap[$id] ?? 0);
        $globalUsed = (int) ($globalUsedMap[$id] ?? 0);

        $disabledReason = null;

        if (strtoupper((string) $d->status) !== 'ACTIVE') {
          $disabledReason = 'Voucher không còn khả dụng.';
        } elseif (!empty($d->start_date) && \Carbon\Carbon::parse($d->start_date)->gt($now)) {
          $disabledReason = 'Voucher chưa đến thời gian phát hành.';
        } elseif (!empty($d->end_date) && \Carbon\Carbon::parse($d->end_date)->lt($now)) {
          $disabledReason = 'Voucher đã hết hạn.';
        } elseif ($d->usage_limit !== null && $globalUsed >= (int) $d->usage_limit) {
          $disabledReason = 'Voucher đã hết lượt sử dụng.';
        } elseif ($d->per_user_limit !== null && $userUsed >= (int) $d->per_user_limit) {
          $disabledReason = 'Bạn đã dùng voucher này tối đa số lần cho phép.';
        }

        $discountText = (int) ($d->discount_percent ?? 0) > 0
          ? (int) $d->discount_percent . '%'
          : $fmtShortMoney($d->discount_amount_vnd ?? $d->value ?? 0);

        $description = $isShip
          ? ((string) ($d->description ?? 'Giảm phí vận chuyển cho đơn hàng'))
          : ((string) ($d->description ?? 'Giảm giá cho đơn hàng'));

        $minText = $fmtVnd($d->min_order_value_vnd ?? 0);
        $expiryText = $fmtDate($d->end_date ?? null);

        $limitText = '';
        if ($d->usage_limit !== null) {
          $remain = max(0, (int) $d->usage_limit - $globalUsed);
          $limitText = "Còn {$remain} lượt";
        }
      @endphp

      <div class="voucher-card position-relative">
        <button
          type="button"
          class="btn btn-link p-0 text-danger position-absolute top-0 end-0 m-2 js-voucher-remove"
          data-id="{{ $id }}"
          aria-label="Xóa voucher"
          title="Xóa voucher"
        >
          <i class="bi bi-trash3 fs-5"></i>
        </button>

        <div class="voucher-header">
          <span class="voucher-type-badge {{ $isShip ? 'badge-ship' : 'badge-order' }}">
            <i class="fas {{ $isShip ? 'fa-shipping-fast' : 'fa-tag' }}"></i>
            {{ $isShip ? 'Giảm Phí Ship' : 'Giảm Giá Đơn Hàng' }}
          </span>

          <div class="voucher-code-display">
            <p class="voucher-code-text">{{ $code }}</p>
          </div>

          <p class="discount-value">{{ $discountText }}</p>
        </div>

        <div class="voucher-body">
          <p class="voucher-description">{{ $description }}</p>

          <div class="voucher-details">
            <div class="detail-item">
              <i class="fas fa-shopping-cart"></i>
              <span>Đơn tối thiểu: <span class="detail-value">{{ $minText }}</span></span>
            </div>

            <div class="detail-item">
              <i class="fas fa-clock"></i>
              <span>
                Hạn sử dụng:
                <span class="expiry-badge"><i class="fas fa-calendar-alt"></i> {{ $expiryText }}</span>
              </span>
            </div>

            @if ($limitText !== '')
              <div class="detail-item">
                <i class="fas fa-layer-group"></i>
                <span><span class="detail-value">{{ $limitText }}</span></span>
              </div>
            @endif
          </div>

          @if ($disabledReason)
            <div class="voucher-disabled-note text-danger">
              {{ $disabledReason }}
            </div>
          @endif
        </div>
      </div>
    @empty
      <div class="empty-state">
        <i class="fas fa-ticket-alt"></i>
        <p>Ví voucher của bạn đang trống</p>
      </div>
    @endforelse
  </div>

  @if ($discounts->hasPages())
    <div class="d-flex justify-content-center mt-4 list-voucher-wallet-pagination">
      {{ $discounts->links('pagination::bootstrap-5') }}
    </div>
  @endif
</div>
@endsection

@push('scripts')
  @vite(['resources/js/pages/voucher-center.js'])
@endpush
