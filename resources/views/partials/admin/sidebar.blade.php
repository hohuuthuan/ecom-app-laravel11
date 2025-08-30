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
  
        {{-- <x-sidebar.submenu icon="fa-users" label="Users" :routes="['admin.users.list', 'admin.users.create']">
          <x-sidebar.item route="admin.users.list"   icon="fa-list" label="Danh sách người dùng" />
          <x-sidebar.item route="admin.users.create" icon="fa-plus" label="Tạo người dùng" />
        </x-sidebar.submenu> --}}
      </ul>
    </nav>
  </div>
</aside>
