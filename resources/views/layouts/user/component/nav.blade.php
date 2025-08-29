<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-3">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="{{ route('home') }}">Ecom</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
      aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="mainNavbar" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        @auth
        @if(method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a>
        </li>
        @endif

        <li class="nav-item">
          <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
        </li>

        <li class="nav-item">
          <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button class="btn btn-outline-danger btn-sm">Logout</button>
          </form>
        </li>
        @else
        <li class="nav-item">
          <a class="btn btn-primary btn-sm me-2" href="{{ route('login.form') }}">Login</a>
        </li>
        <li class="nav-item">
          <a class="btn btn-outline-primary btn-sm" href="{{ route('register.form') }}">Register</a>
        </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>