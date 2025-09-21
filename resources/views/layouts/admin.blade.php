@extends('layouts.base')

@section('head')
@include('partials.admin.head')
@endsection

@section('body_class','admin-page')

@section('layout')

<div class="container-fluid g-0">
  <div class="row g-0">
    @php $mini = (($_COOKIE['adminSidebarMini'] ?? '0') === '1'); @endphp
    @include('partials.admin.sidebar')
    <div id="contentCol" class="col-12 d-flex flex-column min-vh-100 {{ $mini ? 'col-lg-11' : 'col-lg-10' }}">
      @include('partials.admin.header')
      <main class="wrapper-content flex-grow-1">
        @yield('content')
      </main>
      @include('partials.admin.footer')
    </div>
  </div>
</div>

@include('partials.admin.offcanvas-mobile')
@include('partials.admin.script')
@endsection