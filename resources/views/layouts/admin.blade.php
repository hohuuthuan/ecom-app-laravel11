@extends('layouts.base')

@section('head')
  @include('partials.admin.head')
@endsection

@section('body_class','admin-page')

@section('layout')
<div class="container-fluid g-0 admin-layout">
  <div class="row g-0 admin-layout-row flex-lg-nowrap">
    @include('partials.admin.sidebar')

    <div id="contentCol" class="col d-flex flex-column admin-content-col">
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
