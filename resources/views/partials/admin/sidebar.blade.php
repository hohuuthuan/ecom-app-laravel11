<aside id="sidebarCol" class="d-none d-lg-block col-lg-2">
  <div class="sidebar-box">
    @php
    $u = auth()->user();
    $name = $u->full_name ?? 'Admin';
    $avatar = ($u && $u->avatar) ? Storage::url($u->avatar) : asset('storage/user/base-avatar.jpg');
    @endphp

    <div class="px-3 py-3 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
      <img class="rounded-circle" src="{{ $avatar }}" width="38" height="38" alt="{{ $name }}">
      <div class="name-role">
        <div class="fw-bold text-white">{{ $name }}</div>
        <div class="small text-muted">Administrator</div>
      </div>
    </div>

    <nav class="nav-section">
      <ul class="list-unstyled mb-0">
        <x-sidebar.item route="admin.dashboard" icon="fa-home" label="Dashboard" />
        <x-sidebar.item route="admin.accounts.index" icon="fa-users" label="Tài khoản" />
        <x-sidebar.item route="admin.catalog.index" icon="fa-users" label="Danh mục / NSX" />
      </ul>
    </nav>
  </div>
</aside>