@extends('layouts.base')

@section('title', trim($__env->yieldContent('title', 'Admin')))

@push('head')
  @includeIf('partials.admin.head')
@endpush

@section('body')
  <div class="admin-layout">
    @includeIf('partials.admin.navbar')

    <div class="admin-shell">
      @includeIf('partials.admin.sidebar')

      <div class="admin-content">
        @includeIf('partials.breadcrumb')

        <main id="app" data-page="@yield('page_id','')">
          @yield('content')
        </main>

        @includeIf('partials.flash-toasts')
      </div>
    </div>
  </div>

  @includeIf('partials.loading')
@endsection

@push('vendor_scripts')
  {{-- Vendor bundle: jQuery + select2 + CSS qua Vite. Nạp TRƯỚC app.ts --}}
  @vite('resources/js/vendor.ts')
@endpush


