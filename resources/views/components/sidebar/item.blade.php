@props([
  'route',
  'icon' => null,
  'label',
  'href' => null,
  'active' => null,
])

@php
  $patterns = (array) ($active ?? $route);
  $isActive = collect($patterns)->contains(function ($pattern) {
    return request()->routeIs($pattern);
  });

  if ($href) {
    $url = $href;
  } else {
    $routeName = is_array($route) ? $route[0] : $route;
    $url = route($routeName);
  }
@endphp

<li class="nav-item">
  <a
    class="nav-link sidebar-link {{ $isActive ? 'active' : '' }}"
    href="{{ $url }}">
    @if ($icon)
      <i class="fa {{ $icon }} fa-fw sidebar-icon"></i>
    @endif
    <span class="nav-label">{{ $label }}</span>
  </a>
</li>
