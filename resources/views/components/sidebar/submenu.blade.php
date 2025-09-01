@props([
  'icon' => null,
  'label',
  'routes' => [],
])

@php
  use Illuminate\Support\Str;
  $isActive = collect($routes)->contains(fn($r) => request()->routeIs($r));
  $menuId = 'menu-'.Str::slug($label);
@endphp

<li class="nav-item">
  <a class="nav-link sidebar-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
     data-bs-toggle="collapse" href="#{{ $menuId }}"
     aria-expanded="{{ $isActive ? 'true' : 'false' }}">
    @if($icon)<i class="fa {{ $icon }} fa-fw sidebar-icon"></i>@endif
    <span class="nav-label flex-grow-1">{{ $label }}</span>
    <i class="fa fa-angle-right caret ms-auto"></i>
  </a>

  <div class="collapse submenu {{ $isActive ? 'show' : '' }}" id="{{ $menuId }}">
    <ul class="list-unstyled ps-3 my-2">
      {{ $slot }}
    </ul>
  </div>
</li>
