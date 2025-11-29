@extends('layouts.base')

@section('head')
@include('partials.admin.head')
@endsection

@section('body_class','warehouse-page')

@section('layout')

<div class="container-fluid g-0">
  <div class="row h-100 g-0 flex-nowrap">
    <div class="col-auto px-0 warehouse-sidebar">
      @include('partials.admin.warehouseSidebar')
    </div>
    <div class="col p-4 warehouse-main">
      @yield('content')
    </div>
  </div>
</div>


@include('partials.admin.script')
@endsection