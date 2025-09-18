{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')
@section('title','Dashboard')
@section('content')
<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
  <div class="card">
    <div class="card-header flex items-center justify-between">
      <span>Income</span><span class="badge">Monthly</span>
    </div>
    <div class="card-body">
      <div class="text-3xl font-light">40 886,200</div>
      <div class="text-xs text-gray-500 mt-1">Total income <a href="#" class="text-sky-600">98% ↑</a></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header flex items-center justify-between">
      <span>Orders</span><span class="badge">Annual</span>
    </div>
    <div class="card-body">
      <div class="text-3xl font-light">275,800</div>
      <div class="text-xs text-gray-500 mt-1">New orders <a href="#" class="text-emerald-600">20% ↑</a></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header flex items-center justify-between">
      <span>Visits</span><span class="badge">Today</span>
    </div>
    <div class="card-body">
      <div class="text-3xl font-light">106,120</div>
      <div class="text-xs text-gray-500 mt-1">New visits <a href="#" class="text-emerald-600">44% ↑</a></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header flex items-center justify-between">
      <span>User activity</span><span class="badge danger">Low value</span>
    </div>
    <div class="card-body">
      <div class="text-3xl font-light">80,600</div>
      <div class="text-xs text-gray-500 mt-1">In first month <a href="#" class="text-rose-600">38% ↓</a></div>
    </div>
  </div>
</div>
@endsection
