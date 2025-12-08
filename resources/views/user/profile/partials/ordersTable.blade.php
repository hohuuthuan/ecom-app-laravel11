<div class="table-responsive">
  <table class="table table-sm align-middle order-table">
    <thead>
      <tr>
        <th>Mã đơn</th>
        <th>Ngày đặt</th>
        <th>Tổng tiền</th>
        <th>Trạng thái</th>
        <th class="text-center">Đánh giá</th>
        <th class="text-center">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      @if ($orders->count() > 0)
        @foreach ($orders as $order)
          @php
            $statusRaw   = strtoupper($order->status ?? '');
            $statusLabel = 'Không xác định';
            $badgeClass  = 'badge-status-pending';

            if (in_array($statusRaw, [
              'PENDING',
              'PROCESSING',
              'PICKING',
              'SHIPPING',
              // legacy
              'CONFIRMED',
              'SHIPPED',
            ], true)) {
              $statusLabel = 'Đang xử lý';
              $badgeClass  = 'badge-status-pending';
            } elseif (in_array($statusRaw, ['DELIVERED', 'COMPLETED'], true)) {
              $statusLabel = 'Hoàn thành';
              $badgeClass  = 'badge-status-success';
            } elseif (in_array($statusRaw, ['CANCELLED', 'RETURNED', 'DELIVERY_FAILED'], true)) {
              $statusLabel = 'Đã hủy / giao thất bại';
              $badgeClass  = 'badge-status-cancel';
            }

            // Thống kê review theo đơn
            $stats          = $orderReviewStats[$order->id] ?? null;
            $canReview      = false;
            $allReviewed    = false;
            $isFinishStatus = in_array($statusRaw, ['DELIVERED', 'COMPLETED'], true);

            if ($isFinishStatus && $stats !== null) {
              $totalItems    = (int) ($stats['total_items'] ?? 0);
              $reviewedItems = (int) ($stats['reviewed_items'] ?? 0);

              if ($totalItems > 0) {
                if ($reviewedItems < $totalItems) {
                  $canReview = true;
                } elseif ($reviewedItems >= $totalItems) {
                  $allReviewed = true;
                }
              }
            }
          @endphp

          <tr>
            <td>{{ $order->code }}</td>
            <td>
              {{ optional($order->placed_at)
                  ? optional($order->placed_at)->timezone(config('app.timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i')
                  : '—' }}
            </td>
            <td>{{ number_format((int) $order->grand_total_vnd, 0, ',', '.') }}đ</td>
            <td>
              <span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span>
            </td>

            {{-- Cột Đánh giá --}}
            <td class="text-center">
              @if ($canReview)
                {{-- Đơn hoàn thành và còn sản phẩm chưa đánh giá --}}
                <a
                  href="{{ route('user.reviews.order', $order->id) }}"
                  class="btn btn-sm order-review-btn">
                  <i class="bi bi-star-fill"></i>
                  <span>Đánh giá</span>
                </a>
              @elseif ($allReviewed)
                {{-- Đơn hoàn thành và tất cả sản phẩm đã được đánh giá --}}
                <span class="badge rounded-pill bg-success small">
                  <i class="bi bi-check-circle-fill me-1"></i>
                  Đã đánh giá
                </span>
              @else
                {{-- Các trạng thái khác (chưa hoàn thành, đã hủy, v.v.) --}}
                <span class="text-muted small">___</span>
              @endif
            </td>

            {{-- Cột Thao tác --}}
            <td class="text-center">
              <a href="{{ route('user.profile.orders.show', $order->id) }}">
                <i class="fa fa-eye icon-eye-view-order-detail"></i>
              </a>
            </td>
          </tr>
        @endforeach
      @else
        <tr>
          <td colspan="6" class="text-center text-muted">
            Bạn chưa có đơn hàng nào.
          </td>
        </tr>
      @endif
    </tbody>
  </table>
</div>

@if ($orders->hasPages())
  <div
    class="mt-3 d-flex justify-content-center orders-pagination"
    data-profile-orders-pagination>
    {{ $orders->links('pagination::bootstrap-5') }}
  </div>
@endif
