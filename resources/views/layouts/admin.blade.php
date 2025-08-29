@extends('layouts.base')

@section('body_class','admin-page')

@section('layout')
  <div class="d-flex">
    {{-- Sidebar --}}
    @include('partials.admin.sidebar')

    <div class="flex-grow-1 d-flex flex-column">
      {{-- Navbar --}}
      @include('partials.admin.nav')

      <main class="container-fluid py-4 flex-grow-1">
        @yield('content')
      </main>

      @include('partials.admin.footer')
    </div>
  </div>
@endsection
