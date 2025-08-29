<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin')</title>

<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<style>
  body.auth-page{background:#f5f7fa}
  .auth-wrapper{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .auth-card{max-width:420px;width:100%;background:#fff;border:1px solid #e6e9ed;border-radius:6px;padding:24px}
  #page-wrapper .container-fluid{padding:20px}
  .sidebar{border-right:1px solid #eee;min-height:100vh;padding:15px}
</style>
