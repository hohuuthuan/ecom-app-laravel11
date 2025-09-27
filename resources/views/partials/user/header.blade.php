<header class="sticky-top bg-white border-bottom" role="banner">
  <div class="container py-2">
    <div class="d-flex align-items-center gap-3">
      <!-- Logo -->
      <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="{{ route('home') }}" aria-label="LeafBook">
        <span class="logo-circle"><i class="bi bi-journal-richtext"></i></span>
        <strong class="text-dark d-none d-sm-inline">LeafBook</strong>
      </a>

      <!-- Search -->
      <form class="flex-grow-1 search-wrap" role="search" aria-label="Tìm kiếm" method="GET" action="">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input class="form-control" type="search" name="" placeholder="Tìm sách, tác giả, chủ đề..." aria-label="Ô tìm kiếm" value="">
        </div>
      </form>

      <!-- Actions -->
      <nav class="d-flex align-items-center gap-3 ms-auto" aria-label="Tác vụ">
        <a class="text-dark position-relative action" href="" aria-label="Giỏ hàng">
          <i class="bi bi-cart3 fs-4"></i>
          <span class="badge-count" aria-hidden="true">1</span>
        </a>

        @auth
        <div class="dropdown">
          <button class="btn p-0 border-0 bg-transparent d-inline-flex align-items-center gap-2"
            type="button" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Tài khoản">
            <img class="avatar rounded-circle"
              src="{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : asset('storage/avatars/base-avatar.jpg') }}"
              alt="Avatar người dùng">
            <span class="d-none d-md-inline fw-medium">{{ auth()->user()->name ?? auth()->user()->username }}</span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuBtn">
            @if(auth()->user()->hasRole('Admin'))
            <li>
              <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
              </a>
            </li>
            @else
            <li>
              <a class="dropdown-item" href="#">
                <i class="bi bi-person-circle me-2"></i>Profile
              </a>
            </li>
            @endif

            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                  <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                </button>
              </form>
            </li>
          </ul>
        </div>
        @else
        <a href="{{ route('login.form') }}" class="btn btn-primary">Đăng nhập</a>
        @endauth

      </nav>

    </div>

    <!-- Utility bar -->
    <div class="mt-2 p-2 util-row rounded-3">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-grid-3x3-gap text-brand fs-5"></i>
            <div class="dropdown w-100">
              <button class="btn btn-light w-100 d-flex justify-content-between align-items-center" id="catBtn" data-bs-toggle="dropdown" aria-expanded="false">
                Danh mục sách
                <i class="bi bi-chevron-down small ms-2"></i>
              </button>
              <ul class="dropdown-menu w-100" aria-labelledby="catBtn">
                <li><a class="dropdown-item" href="#">Thiếu nhi</a></li>
                <li><a class="dropdown-item" href="#">Văn học</a></li>
                <li><a class="dropdown-item" href="#">Kinh tế</a></li>
                <li><a class="dropdown-item" href="#">Khoa học</a></li>
                <li><a class="dropdown-item" href="#">Sách công nghệ</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-4 col-md-3">
          <a class="util-link" href="#"><i class="bi bi-clock-history me-1"></i>Sản phẩm đã xem</a>
        </div>
        <div class="col-4 col-md-3">
          <div class="util-link"><i class="bi bi-truck me-1"></i>Ship COD toàn quốc</div>
        </div>
        <div class="col-4 col-md-3">
          <a class="util-link" href="tel:19001234"><i class="bi bi-telephone me-1"></i>Hotline: 1900 1234</a>
        </div>
      </div>
    </div>
  </div>
</header>