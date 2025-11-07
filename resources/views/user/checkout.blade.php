@extends('layouts.user')
@section('title','Thanh toán')

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 m-0">Thanh toán</h1>
    <a href="{{ route('cart') }}" class="btn btn-outline-secondary">← Quay lại giỏ hàng</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card p-3">
        @foreach($items as $line)
          <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
              <div class="fw-semibold">{{ $line['title'] }}</div>
              <div class="text-muted small">SL: {{ (int)$line['qty'] }} × {{ number_format($line['price'],0,',','.') }}đ</div>
            </div>
            <div class="text-primary fw-semibold">
              {{ number_format($line['line_total'],0,',','.') }}đ
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card p-3">
        <div class="d-flex justify-content-between mb-2">
          <span>Tạm tính</span>
          <span>{{ number_format($subtotal,0,',','.') }}đ</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Phí vận chuyển</span>
          <span>{{ number_format($shipping,0,',','.') }}đ</span>
        </div>
        <div class="d-flex justify-content-between border-top pt-2 mt-2">
          <span class="fw-semibold">Tổng cộng</span>
          <span class="fw-bold text-primary">{{ number_format($total,0,',','.') }}đ</span>
        </div>

        <form action="#" method="POST" class="mt-3">
          @csrf
          <button type="submit" class="btn btn-primary w-100" disabled>Đặt hàng (coming soon)</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
