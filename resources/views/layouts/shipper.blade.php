@extends('layouts.base')

@section('head')
  @include('partials.admin.head')
@endsection

@section('body_class','shipper-page')

@section('layout')
  @include('partials.shipper.shipperSidebar')

  <div class="shipper-main">
    @yield('content')
  </div>

  @include('partials.admin.script')
@endsection
