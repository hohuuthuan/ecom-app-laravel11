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
            $statusRaw = strtoupper((string) $order->status);
            $statusLabel = 'Không xác định';
            $badgeClass  = 'badge-status-pending';

            // ===== TEXT HIỂN THỊ THEO STATUS MỚI =====
            switch ($statusRaw) {
              case 'PENDING':
                $statusLabel = 'Chờ xử lý';
                break;

              case 'PROCESSING':
                $statusLabel = 'Tiếp nhận đơn, chuyển đơn sang đơn vị kho';
                break;

              case 'PICKING':
                $statusLabel = 'Đang chuẩn bị hàng';
                break;

              case 'SHIPPING':
                $statusLabel = 'Đã giao cho đơn vị vận chuyển';
                break;

              case 'COMPLETED':
                $statusLabel = 'Hoàn tất đơn hàng';
                break;

              case 'CANCELLED':
                $statusLabel = 'Hủy đơn hàng';
                break;

              case 'DELIVERY_FAILED':
                $statusLabel = 'Giao hàng thất bại';
                break;

              case 'RETURNED':
                $statusLabel = 'Hoàn / trả hàng';
                break;
            }

            // ===== MÀU BADGE (GOM NHÓM) =====
            if (in_array($statusRaw, ['PENDING', 'PROCESSING', 'PICKING', 'SHIPPING'], true)) {
              $badgeClass = 'badge-status-pending';   // nhóm đang xử lý
            } elseif ($statusRaw === 'COMPLETED') {
              $badgeClass = 'badge-status-success';   // hoàn thành
            } elseif (in_array($statusRaw, ['CANCELLED', 'DELIVERY_FAILED', 'RETURNED'], true)) {
              $badgeClass = 'badge-status-cancel';    // huỷ / hoàn / fail
            }

            // ===== THỐNG KÊ REVIEW THEO ĐƠN =====
            $stats          = $orderReviewStats[$order->id] ?? null;
            $canReview      = false;
            $allReviewed    = false;

            // Chỉ cho review khi đơn đã hoàn tất
            $isFinishStatus = ($statusRaw === 'COMPLETED');

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
              {{ optional($order->placed_at ?? $order->created_at)
                    ? optional($order->placed_at ?? $order->created_at)
                        ->timezone(config('app.timezone', 'Asia/Ho_Chi_Minh'))
                        ->format('d/m/Y H:i')
                    : '—' }}
            </td>
            <td>{{ number_format((int) $order->grand_total_vnd, 0, ',', '.') }}đ</td>
            <td>
              <span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span>
            </td>

            {{-- Cột Đánh giá --}}
            <td class="text-center">
              @if ($canReview)
                <a
                  href="{{ route('user.reviews.order', $order->id) }}"
                  class="btn btn-sm order-review-btn">
                  <i class="bi bi-star-fill"></i>
                  <span>Đánh giá</span>
                </a>
              @elseif ($allReviewed)
                <span class="badge rounded-pill bg-success small">
                  <i class="bi bi-check-circle-fill me-1"></i>
                  Đã đánh giá
                </span>
              @else
                <span class="text-muted small">Đơn hàng chưa đủ điều kiện</span>
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
