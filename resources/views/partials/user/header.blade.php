<header class="bg-white shadow-sm">
  <nav class="navbar navbar-expand-lg navbar-light container">
    <a class="navbar-brand fw-bold" href="{{ route('home') }}">Ecom Perfume</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Sản phẩm</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Liên hệ</a></li>
      </ul>

      @auth
      <div class="d-flex align-items-center ms-lg-3 gap-2">
        {{-- Chỉ hiện khi là admin --}}
        @if(auth()->user()->hasRole('Admin'))
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
          Dashboard
        </a>
        @endif

        {{-- Logout đứng riêng, không dropdown --}}
        <form action="{{ route('logout') }}" method="POST" class="m-0">
          @csrf
          <button type="submit" class="btn btn-danger">
            Đăng xuất
          </button>
        </form>
      </div>
      @else
      <div class="d-flex align-items-center ms-lg-3 gap-2">
        <a href="{{ route('login.form') }}" class="btn btn-primary">Đăng nhập</a>
        <a href="{{ route('register.form') }}" class="btn btn-outline-primary">Đăng ký</a>
      </div>
      @endauth
    </div>
  </nav>
</header>