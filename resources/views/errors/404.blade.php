@extends('layouts.base')
@section('title','404 Not Found')

@push('styles')
  @vite('resources/css/pages/error.css')
@endpush

@section('body')
  <div class="error-wrap">
    <div class="error-card">
      <div class="error-code">404</div>
      <div class="error-title">Không tìm thấy trang</div>
      <p class="error-desc">Đường dẫn không tồn tại hoặc đã bị di chuyển.</p>
      <a href="{{ route('home') }}" class="btn btn-primary">Về trang chủ</a>
    </div>
  </div>
@endsection
