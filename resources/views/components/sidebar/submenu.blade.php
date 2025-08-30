@props([
'icon' => null,
'label',
'routes' => [],
])

@php
use Illuminate\Support\Str;
$isActive = collect($routes)->contains(fn($r) => request()->routeIs($r));
$menuId = 'menu-'.Str::slug($label); // id duy nhất cho collapse
@endphp

<li class="nav-item {{ $isActive ? 'active' : '' }}">
  <a class="nav-link" data-bs-toggle="collapse" href="#{{ $menuId }}"
    aria-expanded="{{ $isActive ? 'true' : 'false' }}">
    @if($icon)<i class="fa {{ $icon }}"></i>@endif
    <span class="nav-label">{{ $label }}</span>
    <i class="fa fa-angle-right caret"></i>
  </a>

  <div class="collapse submenu {{ $isActive ? 'show' : '' }}" id="{{ $menuId }}">
    <ul class="list-unstyled">
      {{ $slot }}
    </ul>
  </div>
</li>