@extends('layouts.base')

@section('head')
  @include('partials.admin.head')
@endsection

@section('body_class','warehouse-page')

@section('layout')
  @include('partials.admin.warehouseSidebar')
  <div class="warehouse-main">
    @yield('content')
  </div>

  @include('partials.admin.script')
@endsection
