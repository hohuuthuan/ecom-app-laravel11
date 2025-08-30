@props([
  // route name hoặc mảng route name
  'route',
  'icon' => null,
  'label',
  // optional: nếu muốn truyền URL tay (không dùng route)
  'href' => null,
])

@php
  $routes = (array) $route;
  $isActive = collect($routes)->contains(fn($r) => request()->routeIs($r));
  $url = $href ?: (is_string($route) ? route($route) : route($routes[0]));
@endphp

<li class="nav-item {{ $isActive ? 'active' : '' }}">
  <a class="nav-link" href="{{ $url }}">
    @if($icon)<i class="fa {{ $icon }}"></i>@endif
    <span class="nav-label">{{ $label }}</span>
  </a>
</li>
