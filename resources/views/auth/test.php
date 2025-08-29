@extends('layouts.app')
@section('title','Login')
@section('content')
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card">
      <div class="card-header">Login</div>
      <div class="card-body">
        <form method="POST" action="{{ url('/login') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="{{ old('email') }}">
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control">
            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" value="1">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <button class="btn btn-primary w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection