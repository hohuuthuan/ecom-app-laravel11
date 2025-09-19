@php
$user = auth()->user();
$current = request()->route()?->getName();
@endphp

<aside class="sidebar" aria-label="Sidebar">

  @php
  $u = auth()->user();
  $avatar = ($u && $u->avatar)
  ? \Illuminate\Support\Facades\Storage::url($u->avatar)
  : asset('storage/avatars/base-avatar.jpg');
  @endphp

  <div class="sb-user">
    <img src="{{ $avatar }}" alt="{{ $u?->name ?? 'User' }}" class="sb-user-avatar">
    <div class="sb-user-meta">
      <div class="sb-user-name">{{ $u?->name ?? 'User' }}</div>
      <div class="sb-user-email">{{ $u?->email }}</div>
    </div>
  </div>

  <nav class="sb-nav mt-3">
  <a class="sidebar-link {{ $current==='admin.dashboard' ? 'active' : '' }}"
     href="{{ route('admin.dashboard') }}" title="Bảng điều khiển">
    <span class="icon">
      <i class="bi bi-speedometer2 text-xl align-middle" aria-hidden="true"></i>
    </span>
    <span class="sb-label">Dashboard</span>
  </a>

  <a class="sidebar-link {{ str_starts_with($current,'admin.accounts') ? 'active' : '' }}"
     href="{{ route('admin.accounts.index') }}" title="Tài khoản">
    <span class="icon">
      <i class="bi bi-people text-xl align-middle" aria-hidden="true"></i>
    </span>
    <span class="sb-label">Tài khoản</span>
  </a>

  <a class="sidebar-link {{ str_starts_with($current,'admin.catalog') ? 'active' : '' }}"
     href="{{ route('admin.catalog.index') }}" title="Catalog">
    <span class="icon">
      <i class="bi bi-collection text-xl align-middle" aria-hidden="true"></i>
    </span>
    <span class="sb-label">Catalog</span>
  </a>
</nav>

</aside>