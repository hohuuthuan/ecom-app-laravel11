<aside class="warehouse-sidebar d-flex flex-column">
  <div class="p-4 border-bottom flex-shrink-0">
    <h2 class="h4 mb-0 fw-bold text-dark">📦 Quản Lý Kho</h2>
  </div>

  <nav class="mt-4 warehouse_sidebar flex-grow-1 overflow-auto">
    <div class="px-3">
      <a
        href="{{ route('warehouse.dashboard') }}"
        class="warehouse-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ request()->routeIs('warehouse.dashboard') ? 'active' : '' }}">
        <span class="me-3">📊</span>
        <span>Tổng Quan</span>
      </a>

      <a
        href="{{ route('warehouse.orders') }}"
        class="warehouse-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ request()->routeIs(['warehouse.orders', 'warehouse.order.*']) ? 'active' : '' }}">
        <span class="me-3">🧾</span>
        <span>Đơn hàng</span>
      </a>

      <a
        href="{{ route('warehouse.inventory') }}"
        class="warehouse-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ request()->routeIs(['warehouse.inventory', 'warehouse.inventory.*']) ? 'active' : '' }}">
        <span class="me-3">📦</span>
        <span>Tồn Kho</span>
      </a>

      <a
        href="{{ route('warehouse.import') }}"
        class="warehouse-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ request()->routeIs(['warehouse.import', 'warehouse.import.*']) ? 'active' : '' }}">
        <span class="me-3">📥</span>
        <span>Nhập Kho</span>
      </a>

      <a
        href="{{ route('warehouse.purchase_receipts.index') }}"
        class="warehouse-nav-btn btn w-100 text-start p-3 mb-2 d-flex align-items-center {{ request()->routeIs('warehouse.purchase_receipts.*') ? 'active' : '' }}">
        <span class="me-3">📑</span>
        <span>DS phiếu nhập</span>
      </a>
    </div>
  </nav>
</aside>
