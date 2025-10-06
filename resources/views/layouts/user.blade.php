@extends('layouts.base')

@section('head')
  @include('partials.user.head')
@endsection

@section('body_class','user-page')

@section('layout')
  @include('partials.user.header')
    @yield('content')
  @include('partials.user.footer')
  @include('partials.user.script')
@endsection
