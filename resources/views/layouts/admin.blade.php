@extends('layouts.base')

@section('head')
@include('partials.admin.head')
@endsection

@section('body_class','admin-page')

@section('layout')

<div class="container-fluid g-0">
  <div class="row g-0">
    @include('partials.admin.sidebar')
    <div id="contentCol" class="col-12 col-lg-10 d-flex flex-column min-vh-100">
      @include('partials.admin.header')
      <main class="wrapper-content flex-grow-1">
        @yield('content')
      </main>
      @include('partials.admin.footer')
    </div>
  </div>
</div>

@include('partials.admin.offcanvas-mobile')
@include('partials.ui.confirm-modal')
@include('partials.admin.script')
@endsection
