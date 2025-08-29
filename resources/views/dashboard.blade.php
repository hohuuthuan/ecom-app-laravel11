@extends('layouts.user.app')
@section('title','Dashboard')
@section('content')
<div class="p-4 bg-white border rounded">
  <h5 class="mb-0">Hello, {{ auth()->user()->full_name }}</h5>
</div>
@endsection
