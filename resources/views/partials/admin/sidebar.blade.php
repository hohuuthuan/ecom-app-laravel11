<aside id="sidebarCol" class="d-none d-lg-block col-lg-2">
  <div class="sidebar-box">
    <div class="px-3 py-3 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
      <img class="rounded-circle" src="{{ asset('storage/base-avatar.jpg') }}" width="38" height="38" alt="Ảnh người dùng">
      <div class="name-role">
        <div class="fw-bold text-white">{{ auth()->user()->full_name ?? 'Admin' }}</div>
        <div class="small text-muted">Administrator</div>
      </div>
    </div>

    <nav class="nav-section">
      <ul class="list-unstyled mb-0">
        <x-sidebar.item route="admin.dashboard" icon="fa-home" label="Dashboard" />
        <x-sidebar.item route="admin.accounts.index" icon="fa-users" label="Quản lý tài khoản" />
      </ul>
    </nav>
  </div>
</aside>
