@extends('layouts.admin')

@section('title','Admin Dashboard')

@section('body_class','dashboard-page')

@php
  $metrics = $metrics ?? [
      'today' => [
          'revenue_vnd'      => 0,
          'cogs_vnd'         => 0,
          'profit_vnd'       => 0,
          'total_orders'     => 0,
          'delivered_orders' => 0,
      ],
      'month' => [
          'revenue_vnd'            => 0,
          'cogs_vnd'               => 0,
          'profit_vnd'             => 0,
          'total_orders'           => 0,
          'delivered_orders'       => 0,
          'revenue_change_percent' => null,
          'profit_change_percent'  => null,
          'orders_change_percent'  => null,
      ],
      'year' => [
          'revenue_vnd'      => 0,
          'cogs_vnd'         => 0,
          'profit_vnd'       => 0,
          'total_orders'     => 0,
          'delivered_orders' => 0,
      ],
  ];

  $month = $metrics['month'] ?? [];

  $todayPerformance = $todayPerformance ?? [
      'total_today'       => 0,
      'diff_vs_yesterday' => null,
  ];

  if (!function_exists('vnd_format')) {
    function vnd_format($value) {
      return number_format((int) $value, 0, ',', '.') . 'đ';
    }
  }

  $dashboardData = $dashboardData ?? [];
@endphp

@section('content')
  <div class="container-fluid admin-dashboard">
    <script id="adminDashboardPayload" type="application/json">
      {!! json_encode($dashboardData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div class="admin-stats-grid mb-4">
      @php $revChange = $month['revenue_change_percent'] ?? null; @endphp
      <div class="admin-stat-card" id="adminRevenueCard">
        <div class="admin-stat-icon" id="adminRevenueIcon">💰</div>
        <div class="admin-stat-label">Doanh thu tháng này</div>
        <div class="admin-stat-value" id="adminRevenueValue">
          {{ vnd_format($month['revenue_vnd'] ?? 0) }}
        </div>
        <div class="admin-stat-change
          @if($revChange === null)
            admin-stat-change-neutral
          @elseif($revChange > 0)
            admin-stat-change-positive
          @elseif($revChange < 0)
            admin-stat-change-negative
          @else
            admin-stat-change-neutral
          @endif
        ">
          <i class="fas
            @if($revChange === null || $revChange == 0)
              fa-minus
            @elseif($revChange > 0)
              fa-arrow-up
            @else
              fa-arrow-down
            @endif
            me-1"></i>
          <span>
            @if($revChange === null)
              Không có dữ liệu so với tháng trước
            @elseif($revChange > 0)
              +{{ number_format($revChange, 1) }}% so với tháng trước
            @elseif($revChange < 0)
              {{ number_format($revChange, 1) }}% so với tháng trước
            @else
              Không thay đổi so với tháng trước
            @endif
          </span>
        </div>
      </div>

      @php $profitChange = $month['profit_change_percent'] ?? null; @endphp
      <div class="admin-stat-card" id="adminProfitCard">
        <div class="admin-stat-icon" id="adminProfitIcon">📈</div>
        <div class="admin-stat-label">Lợi nhuận tháng này</div>
        <div class="admin-stat-value" id="adminProfitValue">
          {{ vnd_format($month['profit_vnd'] ?? 0) }}
        </div>
        <div class="admin-stat-change
          @if($profitChange === null)
            admin-stat-change-neutral
          @elseif($profitChange > 0)
            admin-stat-change-positive
          @elseif($profitChange < 0)
            admin-stat-change-negative
          @else
            admin-stat-change-neutral
          @endif
        ">
          <i class="fas
            @if($profitChange === null || $profitChange == 0)
              fa-minus
            @elseif($profitChange > 0)
              fa-arrow-up
            @else
              fa-arrow-down
            @endif
            me-1"></i>
          <span>
            @if($profitChange === null)
              Không có dữ liệu so với tháng trước
            @elseif($profitChange > 0)
              +{{ number_format($profitChange, 1) }}% so với tháng trước
            @elseif($profitChange < 0)
              {{ number_format($profitChange, 1) }}% so với tháng trước
            @else
              Không thay đổi so với tháng trước
            @endif
          </span>
        </div>
      </div>

      @php $ordersChange = $month['orders_change_percent'] ?? null; @endphp
      <div class="admin-stat-card" id="adminOrdersCard">
        <div class="admin-stat-icon" id="adminOrdersIcon">📦</div>
        <div class="admin-stat-label">Tổng đơn tháng này</div>
        <div class="admin-stat-value" id="adminOrdersValue">
          {{ number_format((int) ($month['total_orders'] ?? 0), 0, ',', '.') }}
        </div>
        <div class="admin-stat-change
          @if($ordersChange === null)
            admin-stat-change-neutral
          @elseif($ordersChange > 0)
            admin-stat-change-positive
          @elseif($ordersChange < 0)
            admin-stat-change-negative
          @else
            admin-stat-change-neutral
          @endif
        ">
          <i class="fas
            @if($ordersChange === null || $ordersChange == 0)
              fa-minus
            @elseif($ordersChange > 0)
              fa-arrow-up
            @else
              fa-arrow-down
            @endif
            me-1"></i>
          <span>
            @if($ordersChange === null)
              Không có dữ liệu so với tháng trước
            @elseif($ordersChange > 0)
              +{{ number_format($ordersChange, 1) }}% so với tháng trước
            @elseif($ordersChange < 0)
              {{ number_format($ordersChange, 1) }}% so với tháng trước
            @else
              Không thay đổi so với tháng trước
            @endif
          </span>
        </div>
      </div>

      @php
        $todayTotal = (int) ($todayPerformance['total_today'] ?? 0);
        $todayDiff  = $todayPerformance['diff_vs_yesterday'] ?? null;
      @endphp
      <div class="admin-stat-card" id="adminTodayCard">
        <div class="admin-stat-icon" id="adminTodayIcon">🎯</div>
        <div class="admin-stat-label">Đơn hoàn tất hôm nay</div>
        <div class="admin-stat-value" id="adminTodayValue">
          {{ number_format($todayTotal, 0, ',', '.') }}
        </div>
        <div class="admin-stat-change
          @if($todayDiff === null)
            admin-stat-change-neutral
          @elseif($todayDiff > 0)
            admin-stat-change-positive
          @elseif($todayDiff < 0)
            admin-stat-change-negative
          @else
            admin-stat-change-neutral
          @endif
        ">
          <i class="fas
            @if($todayDiff === null || $todayDiff == 0)
              fa-minus
            @elseif($todayDiff > 0)
              fa-arrow-up
            @else
              fa-arrow-down
            @endif
            me-1"></i>
          <span>
            @if($todayDiff === null)
              Không có dữ liệu so với hôm qua
            @elseif($todayDiff > 0)
              +{{ number_format($todayDiff, 0, ',', '.') }} đơn so với hôm qua
            @elseif($todayDiff < 0)
              {{ number_format($todayDiff, 0, ',', '.') }} đơn so với hôm qua
            @else
              Không thay đổi so với hôm qua
            @endif
          </span>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12 col-xl-6">
        <div class="admin-chart-card h-100">
          <div class="admin-chart-header">
            <h3 class="admin-chart-title mb-0">Tỷ lệ giao hàng tháng này</h3>
          </div>
          <div class="admin-chart-container">
            <canvas id="orderStatusChart"></canvas>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-6">
        <div class="admin-chart-card h-100">
          <div class="admin-chart-header">
            <h3 class="admin-chart-title mb-0">Top 5 khách hàng chi tiêu nhiều nhất</h3>
          </div>
          <div class="admin-chart-container">
            <canvas id="topCustomerChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12 col-xl-6">
        <div class="admin-table-card h-100">
          <div class="admin-table-header">
            <h3 class="admin-table-title mb-0">Top sản phẩm bán chạy</h3>
          </div>
          <div id="topProductsList"></div>
        </div>
      </div>

      <div class="col-12 col-xl-6">
        <div class="admin-table-card h-100">
          <div class="admin-table-header">
            <h3 class="admin-table-title mb-0">Sản phẩm sắp hết hàng</h3>
          </div>
          <div id="outOfStockList"></div>
        </div>
      </div>
    </div>

    <div class="admin-chart-card">
      <div class="admin-chart-header">
        <h3 class="admin-chart-title mb-0">Doanh thu, vốn và lợi nhuận 12 tháng</h3>
      </div>
      <div class="admin-chart-container admin-chart-container-lg">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>
  </div>
@endsection
