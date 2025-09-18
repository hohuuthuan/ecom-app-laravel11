{{-- resources/views/partials/admin/sidebar.blade.php --}}
@php
  $user = auth()->user();
  $nav = [
    ['label'=>'Bảng điều khiển','route'=>'admin.dashboard','icon'=>'M3 12h18M3 6h18M3 18h18'],
    ['label'=>'Tài khoản','route'=>'admin.accounts.index','icon'=>'M12 12a5 5 0 100-10 5 5 0 000 10zm-9 9a9 9 0 1118 0H3z'],
    ['label'=>'Catalog','route'=>'admin.catalog.index','icon'=>'M4 6h16M4 12h16M4 18h16'],
  ];
@endphp

<aside id="adminSidebar" class="admin-sidebar" data-collapsible="1">
  <div class="admin-sidebar-inner">
    <div class="flex items-center gap-3">
      <img src="{{ $user?->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed='.urlencode($user?->name ?? 'U') }}"
           alt="Avatar" class="h-12 w-12 rounded-full border object-cover">
      <div class="min-w-0">
        <div class="sidebar-user-name truncate">{{ $user?->name ?? 'User' }}</div>
        <div class="text-xs text-gray-500 truncate">{{ $user?->email }}</div>
      </div>
    </div>

    <nav class="mt-6 space-y-1">
      @foreach($nav as $item)
        @php $active = request()->routeIs($item['route']); @endphp
        <a href="{{ route($item['route']) }}"
           class="nav-link {{ $active ? 'active' : '' }}"
           aria-current="{{ $active ? 'page':'false' }}">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
            <path d="{{ $item['icon'] }}"/>
          </svg>
          <span class="nav-text">{{ $item['label'] }}</span>
        </a>
      @endforeach
    </nav>
  </div>
</aside>
