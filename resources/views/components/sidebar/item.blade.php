@props([
'route',
'icon' => null,
'label',
'href' => null,
])

@php
$routes = (array) $route;
$isActive = collect($routes)->contains(fn($r) => request()->routeIs($r));
$url = $href ?: (is_string($route) ? route($route) : route($routes[0]));
@endphp

<li class="nav-item">
  <a class="nav-link sidebar-link {{ $isActive ? 'active' : '' }}" href="{{ $url }}">
    @if($icon)<i class="fa {{ $icon }} fa-fw sidebar-icon"></i>@endif
    <span class="nav-label">{{ $label }}</span>
  </a>
</li>