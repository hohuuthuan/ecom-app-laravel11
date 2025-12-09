<aside class="shipper-sidebar d-flex flex-column">
  <div class="p-4 border-bottom flex-shrink-0">
    <h2 class="h4 mb-0 fw-bold text-dark">🚚 Giao Hàng</h2>
  </div>

  @php
    $status = strtoupper((string) request()->query('status', 'SHIPPING'));
  @endphp

  <nav class="mt-4 shipper_sidebar flex-grow-1 overflow-auto">
    <div class="px-3">
      <a
        href="{{ route('shipper.dashboard') }}"
        class="shipper-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ request()->routeIs('shipper.dashboard') && $status === 'SHIPPING' ? 'active' : '' }}">
        <span class="me-3">📋</span>
        <span>Đơn đang giao</span>
      </a>

      <a
        href="{{ route('shipper.dashboard', ['status' => 'COMPLETED']) }}"
        class="shipper-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ $status === 'COMPLETED' ? 'active' : '' }}">
        <span class="me-3">✅</span>
        <span>Đơn giao thành công</span>
      </a>

      <a
        href="{{ route('shipper.dashboard', ['status' => 'DELIVERY_FAILED']) }}"
        class="shipper-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ $status === 'DELIVERY_FAILED' ? 'active' : '' }}">
        <span class="me-3">⚠️</span>
        <span>Giao thất bại / hoàn</span>
      </a>
    </div>
  </nav>
</aside>
