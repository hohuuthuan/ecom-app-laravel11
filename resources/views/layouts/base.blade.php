<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>
    @hasSection('title')
    @yield('title') - {{ config('app.name') }}
    @else
    {{ config('app.name') }}
    @endif
  </title>

  @stack('head')

  {{-- Entry CSS mới (CSS thuần) --}}
  @vite(['resources/css/app.css'])

  @stack('styles')
</head>

<body class="@yield('body_class','')">

  @yield('body')

  {{-- Vendor scripts (được push từ layout con, chạy TRƯỚC app.ts) --}}
  @stack('vendor_scripts')

  {{-- Entry JS mới (TypeScript) --}}
  @vite(['resources/js/app.ts'])

  @stack('scripts')
</body>

</html>