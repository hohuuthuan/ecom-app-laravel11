@extends('layouts.admin')

@section('title', 'Báo cáo & thống kê')

@section('body_class')
  @parent admin-report-page
@endsection

@php
  $activeTab = $activeTab ?? request('tab', 'revenue');
  $validTabs = ['revenue', 'order', 'batch'];
  if (!in_array($activeTab, $validTabs, true)) {
      $activeTab = 'revenue';
  }

  $revenueFilters = $revenueFilters ?? [];
  $orderFilters   = $orderFilters ?? [];
  $batchFilters   = $batchFilters ?? [];

  $revenueReport = $revenueReport ?? [
      'chart' => [
          'labels'  => [],
          'revenue' => [],
          'cogs'    => [],
          'profit'  => [],
      ],
      'table' => [],
  ];

  $orderRows = $orderRows ?? [];
  $batchRows = $batchRows ?? [];

  if (!function_exists('vnd_format_report')) {
      function vnd_format_report($value) {
          return number_format((int) $value, 0, ',', '.') . 'đ';
      }
  }
@endphp

@section('content')
  <div class="container-fluid admin-report-page">
    <div class="admin-report-layout">
      <div class="admin-report-main">
        <section
          class="report-section {{ $activeTab === 'revenue' ? 'active' : '' }}"
          data-report-section="revenue">
          <h2 class="report-section-title">
            <i class="fas fa-sack-dollar me-1"></i>
            Báo cáo doanh thu & lợi nhuận
          </h2>
          <p class="report-section-subtitle">
            Tổng quan doanh thu theo thời gian, biểu đồ doanh thu – lợi nhuận, so sánh kỳ trước.
          </p>

          @php
            $revenueTable = $revenueReport['table'] ?? [];
            $sumRev = 0;
            $sumCogs = 0;
            $sumProfit = 0;
            foreach ($revenueTable as $r) {
                $sumRev    += (int) ($r['revenue_vnd'] ?? 0);
                $sumCogs   += (int) ($r['cogs_vnd'] ?? 0);
                $sumProfit += (int) ($r['profit_vnd'] ?? 0);
            }
            $avgMargin = $sumRev > 0 ? round($sumProfit * 100 / $sumRev, 1) : null;
          @endphp

 
            <div class="row g-3 mb-3 report-kpi-row">
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card kpi-primary">
                  <div class="kpi-label">Tổng doanh thu</div>
                  <div class="kpi-value">{{ vnd_format_report($sumRev) }}</div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card kpi-warning">
                  <div class="kpi-label">Tổng giá vốn</div>
                  <div class="kpi-value">{{ vnd_format_report($sumCogs) }}</div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card {{ $sumProfit >= 0 ? 'kpi-success' : 'kpi-danger' }}">
                  <div class="kpi-label">
                    Lợi nhuận gộp
                    @if ($avgMargin !== null)
                      <span class="kpi-sub">({{ number_format($avgMargin, 1, ',', '.') }}%)</span>
                    @endif
                  </div>
                  <div class="kpi-value">{{ vnd_format_report($sumProfit) }}</div>
                </div>
              </div>
            </div>
 

          <div class="card shadow-sm border-0 mb-3 report-filter-card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="mb-0 h6">
                <i class="fas fa-filter me-1 text-primary"></i>
                Bộ lọc thời gian
              </h3>
            </div>
            <div class="card-body">
              <form
                method="GET"
                class="row g-3 align-items-end"
                data-report-filter="revenue">
                <input type="hidden" name="tab" value="revenue">

                <div class="col-md-3">
                  <label class="form-label mb-1">Từ ngày</label>
                  <input
                    type="date"
                    class="form-control form-control-sm"
                    name="revenue_from"
                    value="{{ $revenueFilters['from'] ?? '' }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label mb-1">Đến ngày</label>
                  <input
                    type="date"
                    class="form-control form-control-sm"
                    name="revenue_to"
                    value="{{ $revenueFilters['to'] ?? '' }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label mb-1">Nhóm theo</label>
                  @php
                    $groupBy = $revenueFilters['group_by'] ?? 'day';
                  @endphp
                  <select
                    class="form-select form-select-sm"
                    name="revenue_group_by">
                    <option value="day" {{ $groupBy === 'day' ? 'selected' : '' }}>Ngày</option>
                    <option value="month" {{ $groupBy === 'month' ? 'selected' : '' }}>Tháng</option>
                    <option value="year" {{ $groupBy === 'year' ? 'selected' : '' }}>Năm</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                  <button
                    type="submit"
                    class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-search me-1"></i>
                    Lọc
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm flex-fill js-report-clear">
                    <i class="fas fa-eraser me-1"></i>
                    Xoá lọc
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="card shadow-sm border-0 mb-3 report-chart-card">
            <div class="card-header">
              <h3 class="mb-0 h6">
                <i class="fas fa-chart-column me-1 text-primary"></i>
                Biểu đồ doanh thu – giá vốn – lợi nhuận
              </h3>
            </div>
            <div class="card-body">
              <div class="admin-chart-container admin-chart-container-lg">
                <canvas id="reportRevenueChart"></canvas>
              </div>
              <div
                id="reportRevenueData"
                data-chart='@json($revenueReport["chart"] ?? [], JSON_UNESCAPED_UNICODE)'></div>
            </div>
          </div>

          <div class="card shadow-sm border-0 report-table-card">
            <div class="card-header">
              <h3 class="mb-0 h6">
                <i class="fas fa-table me-1 text-primary"></i>
                Bảng chi tiết doanh thu
              </h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Kỳ</th>
                      <th class="text-end">Doanh thu</th>
                      <th class="text-end">Giá vốn</th>
                      <th class="text-end">Lợi nhuận</th>
                      <th class="text-end">Tỷ suất LN</th>
                    </tr>
                  </thead>
                  <tbody id="reportRevenueTableBody">
                    @forelse ($revenueTable as $row)
                      <tr>
                        <td>{{ $row['period'] }}</td>
                        <td class="text-end">{{ vnd_format_report($row['revenue_vnd']) }}</td>
                        <td class="text-end">{{ vnd_format_report($row['cogs_vnd']) }}</td>
                        <td class="text-end {{ $row['profit_vnd'] < 0 ? 'text-danger fw-semibold' : '' }}">
                          {{ vnd_format_report($row['profit_vnd']) }}
                        </td>
                        <td class="text-end">
                          @if ($row['margin_pct'] !== null)
                            {{ number_format($row['margin_pct'], 1, ',', '.') }}%
                          @else
                            –
                          @endif
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                          Không có dữ liệu trong khoảng thời gian đã chọn.
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                  @if (!empty($revenueTable))
                    @php
                      $tfootProfitClass = $sumProfit < 0 ? 'text-danger fw-semibold' : '';
                    @endphp
                    <tfoot>
                      <tr class="table-light">
                        <th>Tổng</th>
                        <th class="text-end">{{ vnd_format_report($sumRev) }}</th>
                        <th class="text-end">{{ vnd_format_report($sumCogs) }}</th>
                        <th class="text-end {{ $tfootProfitClass }}">
                          {{ vnd_format_report($sumProfit) }}
                        </th>
                        <th class="text-end">
                          @if ($avgMargin !== null)
                            {{ number_format($avgMargin, 1, ',', '.') }}%
                          @else
                            –
                          @endif
                        </th>
                      </tr>
                    </tfoot>
                  @endif
                </table>
              </div>
            </div>
          </div>
        </section>

        <section
          class="report-section {{ $activeTab === 'order' ? 'active' : '' }}"
          data-report-section="order">
          <h2 class="report-section-title">
            <i class="fas fa-receipt me-1"></i>
            Thống kê chi tiết doanh thu và lợi nhuận của đơn hàng
          </h2>
          <p class="report-section-subtitle">
            Thống kê dựa trên các đơn hàng đã hoàn tất hoặc trạng thái được chọn.
          </p>

          @php
            $orderCount       = count($orderRows);
            $orderRevTotal    = 0;
            $orderCogsTotal   = 0;
            $orderProfitTotal = 0;
            foreach ($orderRows as $r) {
                $orderRevTotal    += (int) ($r['revenue_vnd'] ?? 0);
                $orderCogsTotal   += (int) ($r['cogs_vnd'] ?? 0);
                $orderProfitTotal += (int) ($r['profit_vnd'] ?? 0);
            }
          @endphp

       
            <div class="row g-3 mb-3 report-kpi-row">
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card kpi-primary">
                  <div class="kpi-label">Số đơn hàng</div>
                  <div class="kpi-value">{{ number_format($orderCount, 0, ',', '.') }}</div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card kpi-warning">
                  <div class="kpi-label">Tổng doanh thu</div>
                  <div class="kpi-value">{{ vnd_format_report($orderRevTotal) }}</div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card {{ $orderProfitTotal >= 0 ? 'kpi-success' : 'kpi-danger' }}">
                  <div class="kpi-label">Tổng lợi nhuận</div>
                  <div class="kpi-value">{{ vnd_format_report($orderProfitTotal) }}</div>
                </div>
              </div>
            </div>
       

          <div class="card shadow-sm border-0 mb-3 report-filter-card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="mb-0 h6">
                <i class="fas fa-filter me-1 text-primary"></i>
                Bộ lọc đơn hàng
              </h3>
            </div>
            <div class="card-body">
              <form
                method="GET"
                class="row g-3 align-items-end"
                data-report-filter="order">
                <input type="hidden" name="tab" value="order">

                <div class="col-md-3">
                  <label class="form-label mb-1">Từ ngày</label>
                  <input
                    type="date"
                    class="form-control form-control-sm"
                    name="order_from"
                    value="{{ $orderFilters['from'] ?? '' }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label mb-1">Đến ngày</label>
                  <input
                    type="date"
                    class="form-control form-control-sm"
                    name="order_to"
                    value="{{ $orderFilters['to'] ?? '' }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label mb-1">Trạng thái</label>
                  @php
                    $status = $orderFilters['status'] ?? '';
                  @endphp
                  <select
                    class="form-select form-select-sm"
                    name="order_status">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>
                      Tất cả
                    </option>
                    <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>Hoàn tất</option>
                    <option value="DELIVERED" {{ $status === 'DELIVERED' ? 'selected' : '' }}>Đã giao</option>
                    <option value="CANCELLED" {{ $status === 'CANCELLED' ? 'selected' : '' }}>Đã huỷ</option>
                    <option value="RETURNED"  {{ $status === 'RETURNED'  ? 'selected' : '' }}>Hoàn / trả</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                  <button
                    type="submit"
                    class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-search me-1"></i>
                    Lọc
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm flex-fill js-report-clear">
                    <i class="fas fa-eraser me-1"></i>
                    Xoá lọc
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="card shadow-sm border-0 report-table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="mb-0 h6">
                <i class="fas fa-list-ul me-1 text-primary"></i>
                Danh sách đơn hàng
              </h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Mã đơn</th>
                      <th>Khách hàng</th>
                      <th>Ngày hoàn tất</th>
                      <th class="text-end">Doanh thu</th>
                      <th class="text-end">Giá vốn</th>
                      <th class="text-end">Lợi nhuận</th>
                      <th class="text-center">Trạng thái</th>
                    </tr>
                  </thead>
                  <tbody id="reportOrderTableBody">
                    @forelse ($orderRows as $row)
                      @php
                        $profit     = (int) ($row['profit_vnd'] ?? 0);
                        $statusText = strtoupper($row['status'] ?? '');
                        $badgeClass = 'bg-secondary';
                        if ($statusText === 'COMPLETED') {
                          $badgeClass = 'bg-success';
                        } elseif ($statusText === 'DELIVERED') {
                          $badgeClass = 'bg-primary';
                        } elseif (in_array($statusText, ['CANCELLED', 'RETURNED'], true)) {
                          $badgeClass = 'bg-danger';
                        }
                        $deliveredAt = $row['delivered_at'] ?? null;
                        if ($deliveredAt) {
                          $deliveredAt = \Carbon\Carbon::parse($deliveredAt)
                            ->timezone(config('app.timezone', 'Asia/Ho_Chi_Minh'))
                            ->format('d/m/Y H:i');
                        } else {
                          $deliveredAt = '-';
                        }
                      @endphp
                      <tr>
                        <td>{{ $row['code'] ?? '' }}</td>
                        <td>{{ $row['customer_name'] ?? 'Khách lẻ' }}</td>
                        <td>{{ $deliveredAt }}</td>
                        <td class="text-end">{{ vnd_format_report($row['revenue_vnd'] ?? 0) }}</td>
                        <td class="text-end">{{ vnd_format_report($row['cogs_vnd'] ?? 0) }}</td>
                        <td class="text-end {{ $profit < 0 ? 'text-danger fw-semibold' : '' }}">
                          {{ vnd_format_report($profit) }}
                        </td>
                        <td class="text-center">
                          <span class="badge {{ $badgeClass }}">
                            {{ $statusText }}
                          </span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                          Không có đơn hàng phù hợp với bộ lọc.
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                  @if ($orderCount > 0)
                    @php
                      $orderFootProfitCls = $orderProfitTotal < 0 ? 'text-danger fw-semibold' : '';
                    @endphp
                    <tfoot>
                      <tr class="table-light">
                        <th>Tổng</th>
                        <th colspan="2" class="text-end">
                          {{ number_format($orderCount, 0, ',', '.') }} đơn
                        </th>
                        <th class="text-end">{{ vnd_format_report($orderRevTotal) }}</th>
                        <th class="text-end">{{ vnd_format_report($orderCogsTotal) }}</th>
                        <th class="text-end {{ $orderFootProfitCls }}">
                          {{ vnd_format_report($orderProfitTotal) }}
                        </th>
                        <th></th>
                      </tr>
                    </tfoot>
                  @endif
                </table>
              </div>
            </div>
          </div>
        </section>

        <section
          class="report-section {{ $activeTab === 'batch' ? 'active' : '' }}"
          data-report-section="batch">
          <h2 class="report-section-title">
            <i class="fas fa-boxes-stacked me-1"></i>
            Thống kê lô hàng
          </h2>
          <p class="report-section-subtitle">
            Mỗi sản phẩm có thể có nhiều lô: theo dõi SL nhập, SL đã bán, tổng giá nhập, doanh thu và trạng thái hoàn vốn.
          </p>

          @php
            $batchCount        = count($batchRows);
            $sumQtyImport      = 0;
            $sumQtySold        = 0;
            $sumQtyAvail       = 0;
            $sumImportCost     = 0;
            $sumRevenueBatch   = 0;
            $sumProfitBatch    = 0;
            foreach ($batchRows as $r) {
                $qtyImport        = (int) ($r['qty_import'] ?? 0);
                $qtySold          = (int) ($r['qty_sold'] ?? 0);
                $qtyAvail         = (int) ($r['qty_available'] ?? 0);
                $importTotal      = (int) ($r['total_import_cost_vnd'] ?? 0);
                $revenueBatch     = (int) ($r['revenue_vnd'] ?? 0);
                $profitBatch      = (int) ($r['profit_vnd'] ?? 0);

                $sumQtyImport    += $qtyImport;
                $sumQtySold      += $qtySold;
                $sumQtyAvail     += $qtyAvail;
                $sumImportCost   += $importTotal;
                $sumRevenueBatch += $revenueBatch;
                $sumProfitBatch  += $profitBatch;
            }
          @endphp

  
            <div class="row g-3 mb-3 report-kpi-row">
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card kpi-primary">
                  <div class="kpi-label">Số lô hàng</div>
                  <div class="kpi-value">{{ number_format($batchCount, 0, ',', '.') }}</div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card kpi-warning">
                  <div class="kpi-label">
                    Tổng SL nhập / đã bán
                    <span class="kpi-sub">
                      ({{ number_format($sumQtyImport, 0, ',', '.') }} / {{ number_format($sumQtySold, 0, ',', '.') }})
                    </span>
                  </div>
                  <div class="kpi-value">
                    {{ vnd_format_report($sumImportCost) }}
                  </div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6">
                <div class="report-kpi-card {{ $sumProfitBatch >= 0 ? 'kpi-success' : 'kpi-danger' }}">
                  <div class="kpi-label">
                    Doanh thu và lợi nhuận từ lô
                    <span class="kpi-sub">
                      (tồn: {{ number_format($sumQtyAvail, 0, ',', '.') }} sp)
                    </span>
                  </div>
                  <div class="kpi-value">
                    {{ vnd_format_report($sumRevenueBatch) }}
                    <span class="kpi-sub ms-1">
                      / {{ vnd_format_report($sumProfitBatch) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
     

          <div class="card shadow-sm border-0 mb-3 report-filter-card">
            <div class="card-header">
              <h3 class="mb-0 h6">
                <i class="fas fa-filter me-1 text-primary"></i>
                Bộ lọc lô hàng
              </h3>
            </div>
            <div class="card-body">
              <form
                method="GET"
                class="row g-3 align-items-end"
                data-report-filter="batch">
                <input type="hidden" name="tab" value="batch">

                <div class="col-md-3">
                  <label class="form-label mb-1">Sản phẩm</label>
                  <input
                    type="text"
                    name="batch_product"
                    class="form-control form-control-sm"
                    placeholder="Tìm theo tên / mã"
                    value="{{ $batchFilters['product'] ?? '' }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label mb-1">Từ ngày nhập</label>
                  <input
                    type="date"
                    name="batch_from"
                    class="form-control form-control-sm"
                    value="{{ $batchFilters['from'] ?? '' }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                  <button
                    type="submit"
                    class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-search me-1"></i>
                    Lọc
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm flex-fill js-report-clear">
                    <i class="fas fa-eraser me-1"></i>
                    Xoá lọc
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="card shadow-sm border-0 report-table-card">
            <div class="card-header">
              <h3 class="mb-0 h6">
                <i class="fas fa-box-open me-1 text-primary"></i>
                Danh sách lô hàng theo sản phẩm
              </h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Mã lô</th>
                      <th>Sản phẩm</th>
                      <th>Kho</th>
                      <th>Mã phiếu nhập</th>
                      <th>Ngày nhập</th>
                      <th class="text-end">SL nhập</th>
                      <th class="text-end">SL đã bán</th>
                      <th class="text-end">SL còn</th>
                      <th class="text-end">Tổng giá nhập</th>
                      <th class="text-end">Doanh thu</th>
                      <th class="text-end">Lợi nhuận</th>
                      <th class="text-center">Trạng thái hoàn vốn</th>
                    </tr>
                  </thead>
                  <tbody id="reportBatchTableBody">
                    @forelse ($batchRows as $row)
                      @php
                        $received = $row['received_at'] ?? null;
                        if ($received) {
                          $received = \Carbon\Carbon::parse($received)
                            ->timezone(config('app.timezone', 'Asia/Ho_Chi_Minh'))
                            ->format('d/m/Y');
                        } else {
                          $received = '-';
                        }

                        $profit      = (int) ($row['profit_vnd'] ?? 0);
                        $roiStatus   = (string) ($row['roi_status'] ?? 'N/A');
                        $roiBadgeCls = 'bg-secondary';
                        if ($roiStatus === 'Đã hoàn vốn') {
                          $roiBadgeCls = 'bg-success';
                        } elseif ($roiStatus === 'Chưa hoàn vốn') {
                          $roiBadgeCls = 'bg-warning text-dark';
                        } elseif ($roiStatus === 'Lỗ') {
                          $roiBadgeCls = 'bg-danger';
                        }
                      @endphp
                      <tr>
                        <td>{{ $row['batch_id'] ?? '' }}</td>
                        <td>
                          {{ $row['product_name'] ?? '' }}
                          @if (!empty($row['product_code']))
                            <span class="text-muted small">
                              ({{ $row['product_code'] }})
                            </span>
                          @endif
                        </td>
                        <td>{{ $row['warehouse_name'] ?? '' }}</td>
                        <td>{{ $row['receipt_code'] ?? '-' }}</td>
                        <td>{{ $received }}</td>
                        <td class="text-end">
                          {{ number_format($row['qty_import'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                          {{ number_format($row['qty_sold'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                          {{ number_format($row['qty_available'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                          {{ vnd_format_report($row['total_import_cost_vnd'] ?? 0) }}
                        </td>
                        <td class="text-end">
                          {{ vnd_format_report($row['revenue_vnd'] ?? 0) }}
                        </td>
                        <td class="text-end {{ $profit < 0 ? 'text-danger fw-semibold' : '' }}">
                          {{ vnd_format_report($profit) }}
                        </td>
                        <td class="text-center">
                          <span class="badge {{ $roiBadgeCls }}">
                            {{ $roiStatus }}
                          </span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="12" class="text-center text-muted py-4">
                          Không có lô hàng phù hợp với bộ lọc.
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                  @if ($batchCount > 0)
                    @php
                      $batchFootProfitCls = $sumProfitBatch < 0 ? 'text-danger fw-semibold' : '';
                    @endphp
                    <tfoot>
                      <tr class="table-light">
                        <th>Tổng</th>
                        <th colspan="2" class="text-end">
                          {{ number_format($batchCount, 0, ',', '.') }} lô
                        </th>
                        <th></th>
                        <th></th>
                        <th class="text-end">
                          {{ number_format($sumQtyImport, 0, ',', '.') }}
                        </th>
                        <th class="text-end">
                          {{ number_format($sumQtySold, 0, ',', '.') }}
                        </th>
                        <th class="text-end">
                          {{ number_format($sumQtyAvail, 0, ',', '.') }}
                        </th>
                        <th class="text-end">
                          {{ vnd_format_report($sumImportCost) }}
                        </th>
                        <th class="text-end">
                          {{ vnd_format_report($sumRevenueBatch) }}
                        </th>
                        <th class="text-end {{ $batchFootProfitCls }}">
                          {{ vnd_format_report($sumProfitBatch) }}
                        </th>
                        <th></th>
                      </tr>
                    </tfoot>
                  @endif
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>

      <aside class="admin-report-nav">
        <div class="admin-report-nav-title">
          <i class="fas fa-layer-group"></i>
          Điều hướng
        </div>

        <ul class="admin-report-nav-list">
          <li class="admin-report-nav-item">
            <a
              href="{{ route('admin.reports.index', ['tab' => 'revenue']) }}"
              class="admin-report-nav-link {{ $activeTab === 'revenue' ? 'active' : '' }}"
              data-report-target="revenue">
              <i class="fas fa-sack-dollar"></i>
              <div class="nav-text">
                <span class="nav-text-main">Doanh thu & Lợi nhuận</span>
                <span class="nav-text-sub">Báo cáo chi tiết</span>
              </div>
            </a>
          </li>
          <li class="admin-report-nav-item">
            <a
              href="{{ route('admin.reports.index', ['tab' => 'order']) }}"
              class="admin-report-nav-link {{ $activeTab === 'order' ? 'active' : '' }}"
              data-report-target="order">
              <i class="fas fa-receipt"></i>
              <div class="nav-text">
                <span class="nav-text-main">Thống kê đơn hàng</span>
                <span class="nav-text-sub">Chi tiết theo từng đơn</span>
              </div>
            </a>
          </li>
          <li class="admin-report-nav-item">
            <a
              href="{{ route('admin.reports.index', ['tab' => 'batch']) }}"
              class="admin-report-nav-link {{ $activeTab === 'batch' ? 'active' : '' }}"
              data-report-target="batch">
              <i class="fas fa-boxes-stacked"></i>
              <div class="nav-text">
                <span class="nav-text-main">Thống kê lô hàng</span>
                <span class="nav-text-sub">Hoàn vốn theo từng lô</span>
              </div>
            </a>
          </li>
        </ul>
      </aside>
    </div>
  </div>
@endsection

@push('scripts')
  @vite(['resources/js/pages/admin-report.js'])
@endpush
