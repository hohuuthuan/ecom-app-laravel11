<aside
  id="sidebarCol"
  class="col-auto">
  <div class="sidebar-box">
    @php
    $user = auth()->user();

    $name = $user->full_name ?? 'Admin';
    $email = $user->email ?? '';

    $avatarFile = $user && $user->avatar ? $user->avatar : 'base-avatar.jpg';
    $avatarPath = 'avatars/' . $avatarFile;

    $avatar = Storage::disk('public')->url($avatarPath);

    if (!Storage::disk('public')->exists($avatarPath)) {
    $avatar = asset('storage/avatars/base-avatar.jpg');
    }
    @endphp

    {{-- Header user --}}
    <div class="px-3 py-3 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
      <img
        class="rounded-circle"
        src="{{ $avatar }}"
        width="50"
        height="50"
        alt="{{ $name }}">
      <div class="name-role">
        {{-- <div class="fw-bold text-white">{{ $name }}
      </div> --}}
      {{-- <span class="sidebar-email">{{ $email }}</span> --}}
    </div>
  </div>

  {{-- Menu chính --}}
  <nav class="nav-section mt-2">
    <ul class="list-unstyled mb-0">
      <x-sidebar.item
        route="admin.dashboard"
        icon="fa-home"
        label="Dashboard" />

      <x-sidebar.item
        route="admin.reports.index"
        active="admin.reports.*"
        icon="fa-chart-line"
        label="Báo cáo" />

      <x-sidebar.item
        route="admin.accounts.index"
        icon="fa-users"
        label="Tài khoản" />

      <x-sidebar.item
        route="admin.catalog.index"
        active="admin.catalog.*"
        icon="fa-tags"
        label="Catalog" />

      <x-sidebar.item
        route="admin.product.index"
        active="admin.product.*"
        icon="fa-book"
        label="Sản phẩm" />

      <x-sidebar.item
        route="admin.order.index"
        active="admin.order.*"
        icon="fa-receipt"
        label="Đơn hàng" />

      <x-sidebar.item
        route="admin.discount.index"
        active="admin.discount.*"
        icon="fa-ticket-alt"
        label="Mã giảm giá" />

      <x-sidebar.item
        route="admin.review.index"
        active="admin.review.*"
        icon="fa-star"
        label="Đánh giá" />
    </ul>
  </nav>

  <div class="mt-auto sidebar-switch-wrapper">
    <a
      href="{{ route('warehouse.dashboard') }}"
      class="btn btn-warehouse-switch w-100 d-flex align-items-center justify-content-center gap-2"
      title="Đi tới màn hình Kho hàng"
      target="_blank"
      rel="noopener noreferrer">
      <span class="switch-icon d-inline-flex align-items-center justify-content-center">
        <i class="fa fa-warehouse fa-fw"></i>
      </span>
      <span class="switch-label">
        Kho hàng
      </span>
    </a>
  </div>
  </div>
</aside>