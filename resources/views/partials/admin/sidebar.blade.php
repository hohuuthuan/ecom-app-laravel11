<aside id="sidebarCol" class="d-none d-lg-block {{ $mini ? 'col-lg-1 sidebar-mini' : 'col-lg-1  ' }}">
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

    <div class="px-3 py-3 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
      <img class="rounded-circle" src="{{ $avatar }}" width="50" height="50" alt="{{ $name }}">
      <div class="name-role">
        {{--<div class="fw-bold text-white">{{ $name }}
      </div>--}}
      {{--<span class="sidebar-email">{{ $email }}</span>--}}
    </div>
  </div>

  <nav class="nav-section">
    <ul class="list-unstyled mb-0">
      <x-sidebar.item route="admin.dashboard" icon="fa-home" label="Dashboard" />
      <x-sidebar.item route="admin.accounts.index" icon="fa-users" label="Tài khoản" />
      <x-sidebar.item route="admin.catalog.index" icon="fa-tags" label="Catalog" />
      <x-sidebar.item route="admin.product.index" icon="fa-book" label="Sản phẩm" />
      <x-sidebar.item route="admin.order.index" icon="fa-receipt" label="Đơn hàng" />
    </ul>
  </nav>
  </div>
</aside>