@extends('layouts.base')

@section('body_class','user-page')

@section('layout')
  @include('partials.user.header')

  <main class="container py-4">
    @yield('content')
  </main>

  @include('partials.user.footer')
@endsection
