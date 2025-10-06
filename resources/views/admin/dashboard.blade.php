@extends('layouts.admin')

@section('title','Admin Dashboard')

@section('body_class','dashboard-page')

@section('content')
<div class="container-fluid">
  <div class="row g-3">
    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Income</h6>
          <span class="badge rounded-pill text-bg-success">Monthly</span>
        </div>
        <div class="card-body">
          <h2 class="mb-1 fw-semibold">40 886,200</h2>
          <div class="text-success fw-bold">98% <i class="fa fa-bolt"></i></div>
          <small class="text-muted">Total income</small>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Orders</h6>
          <span class="badge rounded-pill text-bg-info">Annual</span>
        </div>
        <div class="card-body">
          <h2 class="mb-1 fw-semibold">275,800</h2>
          <div class="text-info fw-bold">20% <i class="fa fa-level-up-alt"></i></div>
          <small class="text-muted">New orders</small>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Visits</h6>
          <span class="badge rounded-pill text-bg-primary">Today</span>
        </div>
        <div class="card-body">
          <h2 class="mb-1 fw-semibold">106,120</h2>
          <div class="text-primary fw-bold">44% <i class="fa fa-level-up-alt"></i></div>
          <small class="text-muted">New visits</small>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">User activity</h6>
          <span class="badge rounded-pill text-bg-danger">Low value</span>
        </div>
        <div class="card-body">
          <h2 class="mb-1 fw-semibold">80,600</h2>
          <div class="text-danger fw-bold">38% <i class="fa fa-level-down-alt"></i></div>
          <small class="text-muted">In first month</small>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection