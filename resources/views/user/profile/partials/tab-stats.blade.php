<div
  class="profile-tab-content"
  id="tab-stats"
  role="tabpanel"
  aria-labelledby="tab-stats-link">

  @php
    $stats = $stats ?? [];

    $totalOrders       = (int) ($stats['total_orders'] ?? 0);
    $deliveredOrders   = (int) ($stats['delivered_orders'] ?? 0);
    $cancelledOrders   = (int) ($stats['cancelled_orders'] ?? 0);
    $totalSpentVnd     = (int) ($stats['total_spent_vnd'] ?? 0);
    $avgOrderValueVnd  = (int) ($stats['avg_order_value_vnd'] ?? 0);
    $recentOrdersCount = (int) ($stats['recent_orders_count'] ?? 0);
    $recentSpentVnd    = (int) ($stats['recent_spent_vnd'] ?? 0);

    /** @var array<string,int> $statusCounts */
    $statusCounts = $stats['status_counts'] ?? [];

    /** @var array<int,array<string,mixed>> $topProducts */
    $topProducts = $stats['top_products'] ?? [];
  @endphp

  @if ($totalOrders === 0)
    {{-- EMPTY STATE --}}
    <div class="text-center py-5">
      <div class="mb-3">
        <i class="bi bi-graph-up-arrow fs-1 text-muted"></i>
      </div>
      <h5 class="mb-2">Chưa có dữ liệu thống kê</h5>
      <p class="text-muted mb-3">
        Khi bạn đặt đơn hàng đầu tiên, hệ thống sẽ hiển thị thống kê chi tiết tại đây.
      </p>
      <a
        href="{{ route('home') }}"
        class="btn btn-primary">
        Bắt đầu mua sách ngay
      </a>
    </div>
  @else
    {{-- TỔNG QUAN --}}
    <section class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h2 class="profile-section-title mb-1 d-flex align-items-center">
            <i class="bi bi-graph-up-arrow me-2"></i>
            Thống kê đơn hàng
          </h2>
          <p class="profile-section-subtitle mb-0 text-muted">
            Tổng quan chi tiêu và tình trạng đơn hàng của bạn.
          </p>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-3 col-sm-6">
          <div class="card h-100 shadow-sm border-0 stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="profile-info-item-label">Tổng số đơn</span>
                <span class="text-primary">
                  <i class="bi bi-receipt-cutoff"></i>
                </span>
              </div>
              <div class="fs-4 fw-semibold">
                {{ $totalOrders }}
              </div>
              <div class="small text-muted mt-1">
                Đã hoàn thành:
                <span class="fw-semibold">{{ $deliveredOrders }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6">
          <div class="card h-100 shadow-sm border-0 stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="profile-info-item-label">Tổng chi tiêu</span>
                <span class="text-success">
                  <i class="bi bi-cash-coin"></i>
                </span>
              </div>
              <div class="fs-4 fw-semibold text-success">
                {{ number_format($totalSpentVnd, 0, ',', '.') }}đ
              </div>
              <div class="small text-muted mt-1">
                Trung bình / đơn:
                <span class="fw-semibold">
                  {{ number_format($avgOrderValueVnd, 0, ',', '.') }}đ
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6">
          <div class="card h-100 shadow-sm border-0 stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="profile-info-item-label">30 ngày gần đây</span>
                <span class="text-info">
                  <i class="bi bi-calendar-event"></i>
                </span>
              </div>
              <div class="fs-4 fw-semibold ">
                {{ $recentOrdersCount }} đơn
              </div>
              <div class="small text-muted mt-1">
                Đã chi:
                <span class="fw-semibold">
                  {{ number_format($recentSpentVnd, 0, ',', '.') }}đ
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6">
          <div class="card h-100 shadow-sm border-0 stats-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="profile-info-item-label">Đơn bị huỷ / trả</span>
                <span class="text-danger">
                  <i class="bi bi-x-octagon"></i>
                </span>
              </div>
              <div class="fs-4 fw-semibold text-danger">
                {{ $cancelledOrders }} đơn
              </div>
              @php
                $cancelRate = $totalOrders > 0
                  ? round($cancelledOrders * 100 / $totalOrders)
                  : 0;
              @endphp
              <div class="small text-muted mt-1">
                Tỉ lệ:
                <span class="fw-semibold">{{ $cancelRate }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- BIỂU ĐỒ + TOP SẢN PHẨM --}}
    <section class="mb-4">
      <div class="row g-3">
        {{-- BIỂU ĐỒ TRẠNG THÁI ĐƠN --}}
        <div class="col-lg-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-0 pb-0">
              <h3 class="h6 mb-1 d-flex align-items-center">
                <i class="bi bi-pie-chart-fill me-2"></i>
                Phân bố trạng thái đơn hàng
              </h3>
              <p class="text-muted small mb-0">
                Tỉ lệ các đơn theo từng trạng thái xử lý.
              </p>
            </div>
            <div class="card-body">
              @php
                $statusLabels = [
                  'PENDING'   => 'Chờ xử lý',
                  'CONFIRMED' => 'Đã xác nhận',
                  'PICKING'   => 'Đang lấy hàng',
                  'SHIPPED'   => 'Đã gửi đi',
                  'PROCESSING'=> 'Đang xử lý',
                  'SHIPPING'  => 'Đang giao hàng',
                  'DELIVERED' => 'Hoàn thành',
                  'COMPLETED' => 'Đã hoàn tất',
                  'CANCELLED' => 'Đã huỷ',
                  'RETURNED'  => 'Đã trả hàng',
                ];

                $chartLabels = [];
                $chartValues = [];

                foreach ($statusLabels as $key => $label) {
                    $count = (int) ($statusCounts[$key] ?? 0);
                    if ($count > 0) {
                        $chartLabels[] = $label;
                        $chartValues[] = $count;
                    }
                }
              @endphp

              @if (empty($chartLabels))
                <p class="text-muted mb-0">
                  Hiện chưa có đủ dữ liệu để vẽ biểu đồ trạng thái đơn hàng.
                </p>
              @else
                <div
                  id="orderStatusChartContainer"
                  data-chart-labels='@json($chartLabels)'
                  data-chart-values='@json($chartValues)'
                  style="height: 260px;">
                  <canvas id="orderStatusChart"></canvas>
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- TOP SẢN PHẨM --}}
        <div class="col-lg-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-0 pb-0">
              <h3 class="h6 mb-1 d-flex align-items-center">
                <i class="bi bi-star-fill me-2 text-warning"></i>
                Sản phẩm mua nhiều nhất
              </h3>
              <p class="text-muted small mb-0">
                Dựa trên tổng số lượng đã mua trong các đơn hoàn thành.
              </p>
            </div>
            <div class="card-body">
              @if (empty($topProducts))
                <p class="text-muted mb-0">
                  Chưa có dữ liệu đủ để thống kê sản phẩm nổi bật.
                </p>
              @else
                <ul class="list-group list-group-flush">
                  @foreach ($topProducts as $index => $row)
                    @php
                      $rank      = $index + 1;
                      $title     = (string) ($row['title'] ?? 'Sản phẩm');
                      $totalQty  = (int) ($row['total_qty'] ?? 0);
                      $lastAt    = $row['last_order_at'] ?? null;
                      $productId = (string) ($row['id'] ?? '');
                      $slug      = (string) ($row['slug'] ?? '');
                    @endphp
                    <li class="list-group-item px-0">
                      <div class="d-flex align-items-center">
                        <div class="me-3">
                          <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold">
                            #{{ $rank }}
                          </span>
                        </div>
                        <div class="flex-grow-1">
                          <div class="fw-semibold text-truncate" title="{{ $title }}">
                            @if ($productId !== '' && $slug !== '')
                              <a
                                href="{{ route('product.detail', ['slug' => $slug, 'id' => $productId]) }}"
                                class="text-decoration-none text-body"
                                target="_blank"
                                rel="noopener">
                                {{ $title }}
                              </a>
                            @else
                              {{ $title }}
                            @endif
                          </div>
                          <div class="text-muted small">
                            Đã mua:
                            <span class="fw-semibold">{{ $totalQty }}</span> lượt
                            @if ($lastAt)
                              @php
                                $lastDate = $lastAt instanceof \Carbon\Carbon
                                  ? $lastAt
                                  : \Carbon\Carbon::parse($lastAt);
                              @endphp
                              · Lần gần nhất: {{ $lastDate->format('d/m/Y') }}
                            @endif
                          </div>
                        </div>
                      </div>
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif
</div>

@push('scripts')
  {{-- Chart.js CDN --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var container = document.getElementById("orderStatusChartContainer");
      if (!container || typeof Chart === "undefined") {
        return;
      }

      var labels;
      var values;

      try {
        labels = JSON.parse(container.getAttribute("data-chart-labels") || "[]" );
        values = JSON.parse(container.getAttribute("data-chart-values") || "[]" );
      } catch (e) {
        return;
      }

      if (!labels.length || !values.length) {
        return;
      }

      var total = values.reduce(function (sum, val) {
        return sum + (parseInt(val, 10) || 0);
      }, 0);

      if (!total) {
        return;
      }

      var canvas = container.querySelector("canvas");
      if (!canvas) {
        return;
      }

      var ctx = canvas.getContext("2d");

      new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: labels,
          datasets: [
            {
              data: values,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  var value = context.parsed || 0;
                  var percent = total ? Math.round((value * 100) / total) : 0;
                  return context.label + ": " + value + " đơn (" + percent + "%)";
                },
              },
            },
          },
        },
      });
    });
  </script>
@endpush
