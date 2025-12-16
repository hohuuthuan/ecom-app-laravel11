@extends('layouts.user')

@section('title', 'Kho Voucher')


@section('content')
<div class="container my-4 py-2" data-claim-url="{{ route('vouchers.claim') }}">

    <div class="page-header">
        <div class="page-nav">
            <a class="btn continue-shopping" href="{{ route('home') }}">
                <i class="bi bi-arrow-left"></i>
                Quay lại trang chủ
            </a>
        </div>
        <h1 class="pageTitle"><i class="bi bi-cart-fill"></i> KHO VOUCHERS </h1>
    </div>

    <div class="voucher-container">
        <h2 class="section-title">
            <i class="fas fa-fire"></i> Voucher Đang Phát Hành
        </h2>

        <div class="voucher-grid" id="voucherGrid">
            @forelse ($discounts as $d)
            @php
            // Xử lý Logic hiển thị dữ liệu (giữ nguyên logic cũ nhưng mapping sang UI mới)
            $type = strtoupper((string) $d->type);
            $isShip = $type === 'SHIPPING' || $type === 'SHIP';
            $code = strtoupper((string) $d->code);

            $saved = !empty($savedMap[(string) $d->id]);
            $userUsed = (int) ($userUsedMap[(string) $d->id] ?? 0);
            $globalUsed = (int) ($globalUsedMap[(string) $d->id] ?? 0);

            $perUserLimit = $d->per_user_limit !== null ? (int) $d->per_user_limit : null;
            $usageLimit = $d->usage_limit !== null ? (int) $d->usage_limit : null;

            // Logic disable
            $disabledReason = null;
            if ($perUserLimit !== null && $userUsed >= $perUserLimit) {
            $disabledReason = 'Bạn đã dùng tối đa số lần';
            }
            if ($usageLimit !== null && $globalUsed >= $usageLimit) {
            $disabledReason = 'Đã hết lượt sử dụng';
            }

            // Text giảm giá
            $discountText = null;
            if ($type === 'PERCENT' || $type === 'PERCENTAGE') {
            $discountText = ((int) $d->value) . '%';
            } else {
            $discountText = $d->value % 1000 === 0
            ? (string) ((int) ($d->value / 1000)) . 'K'
            : number_format((int) $d->value, 0, ',', '.') . 'đ';
            }

            // Mô tả
            $description = $isShip
            ? ('Giảm ' . $discountText . ' phí vận chuyển')
            : ('Giảm ' . $discountText . ' cho đơn hàng');

            // Min text
            $minText = $d->min_order_value_vnd !== null
            ? number_format((int) $d->min_order_value_vnd, 0, ',', '.') . 'đ'
            : '0đ';

            // Expiry text
            $expiryText = $d->end_date
            ? $d->end_date->format('d/m/Y')
            : 'Vô hạn';

            // Limit text (Map vào slot "Giảm tối đa/Giới hạn" của UI mới)
            $limitText = $perUserLimit !== null
            ? ('Của bạn: ' . max(0, $perUserLimit - $userUsed) . ' lượt')
            : ($usageLimit !== null
            ? ('Còn lại: ' . max(0, $usageLimit - $globalUsed) . ' lượt')
            : 'Không giới hạn');

            $isDisabled = ($saved || $disabledReason);
            @endphp

            <div class="voucher-card">
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
                            <i class="fas fa-gift"></i>
                            <span>Giới hạn: <span class="detail-value">{{ $limitText }}</span></span>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>
                                Hạn sử dụng:
                                <span class="expiry-badge">
                                    <i class="fas fa-calendar-alt"></i> {{ $expiryText }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="save-btn js-voucher-save {{ $saved ? 'saved' : '' }}"
                        data-code="{{ $code }}"
                        {{ $isDisabled ? 'disabled' : '' }}>
                        <i class="fas {{ $saved ? 'fa-check-circle' : 'fa-wallet' }}"></i>
                        <span class="js-btn-text">{{ $saved ? 'Đã lưu vào ví' : 'Lưu vào ví' }}</span>
                    </button>

                    @if (!$saved && $disabledReason)
                    <div class="voucher-disabled-note">{{ $disabledReason }}</div>
                    @endif
                </div>
            </div>

            @empty
            <div class="empty-state">
                <i class="fas fa-ticket-alt"></i>
                <p>Hiện chưa có voucher nào</p>
            </div>
            @endforelse
        </div>
        @if($discounts instanceof \Illuminate\Pagination\LengthAwarePaginator && $discounts->hasPages())
    <div class="mt-3 d-flex justify-content-center list-voucher-pagination">
      {{ $discounts->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
    @endif

    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/pages/voucher-center.js'])
@endpush