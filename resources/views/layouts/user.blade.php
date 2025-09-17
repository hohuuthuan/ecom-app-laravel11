@extends('layouts.base')

@section('body')
  <div class="user-layout">
    <main id="app" data-page="@yield('page_id','')">
      @yield('content')
    </main>

  </div>
@endsection

@push('vendor_scripts')
  @vite('resources/js/vendor.ts')
@endpush
