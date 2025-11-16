<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#" onclick="goHome()">
      <i class="fas fa-book-open text-primary me-2"></i>BookStore
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('home') }}">Trang chủ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('product.list') }}">Sản phẩm</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="navigateToPage('about')">Giới thiệu</a>
        </li>
      </ul>

      <div class="d-flex align-items-center">
        <div class="input-group me-3" style="width: 300px;">
          <input type="text" class="form-control" placeholder="Tìm kiếm sách..." id="searchInput">
          <button class="btn btn-outline-primary" type="button" onclick="performSearch()">
            <i class="fas fa-search"></i>
          </button>
        </div>

        <div class="d-flex align-items-center">
          <!-- Wishlist -->
          @php
          $wishlistCount = auth()->check()
          ? auth()->user()->favorites()->count()
          : 0;
          @endphp

          <a href="{{ route('listFavoriteProduct') }}" class="text-decoration-none me-3 position-relative">
            <i class="fas fa-heart text-danger" style="font-size: 1.2rem;"></i>
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"
              id="wishlistCount" style="font-size: 0.7rem;">
              {{ $wishlistCount }}
            </span>
          </a>

          <!-- Cart -->
          @php
          $cart = session('cart', ['items' => []]);
          $cartItems = is_array($cart['items'] ?? null) ? $cart['items'] : [];
          $cartCount = count($cartItems);
          @endphp

          <a href="{{ route('cart') }}" class="text-decoration-none me-3 position-relative">
            <i class="fas fa-shopping-cart text-primary" style="font-size: 1.2rem;"></i>
            <span id="cartCount"
              class="badge bg-primary position-absolute top-0 start-100 translate-middle"
              style="font-size: 0.7rem;"
              data-count-url="{{ route('cart.count') }}">{{ $cartCount }}</span>
          </a>

          @auth
          <!-- User Avatar Dropdown -->
          <div class="dropdown">

            @php
            $user = auth()->user();
            $displayName = $user->full_name ?? $user->name ?? $user->username ?? 'Admin';
            $email = $user->email ?? '';
            $avatarFile = $user && $user->avatar ? basename($user->avatar) : 'base-avatar.jpg';
            $avatarPath = 'avatars/' . $avatarFile;
            $avatarUrl = Storage::disk('public')->exists($avatarPath)
            ? Storage::disk('public')->url($avatarPath)
            : asset('storage/avatars/base-avatar.jpg');
            @endphp

            <a href="#" class="dropdown-toggle text-decoration-none d-flex align-items-center"
              data-bs-toggle="dropdown" aria-expanded="false">
              <img
                src="{{ $avatarUrl }}"
                alt="Avatar {{ $displayName }}"
                class="rounded-circle me-2"
                style="width:35px;height:35px;object-fit:cover;">
              <span class="text-dark fw-medium d-none d-md-inline">
                {{ $displayName }}
              </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li>
                <div class="dropdown-header">
                  <div class="d-flex align-items-center">
                    <img
                      src="{{ $avatarUrl }}"
                      alt="Avatar {{ $displayName }}"
                      class="rounded-circle me-2"
                      style="width:40px;height:40px;object-fit:cover;">
                    <div>
                      <div class="fw-bold">{{ $displayName }}</div>
                      <small class="text-muted">{{ $email }}</small>
                    </div>
                  </div>
                </div>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>

              @if(auth()->user()->hasRole('Admin'))
              <li>
                <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                  <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
              </li>
              @endif

              <li>
                <a class="dropdown-item" href="{{ route('user.profile.index') }}">
                  <i class="fas fa-user me-2"></i>Hồ sơ
                </a>
              </li>

              <li>
                <hr class="dropdown-divider">
              </li>

              <!-- Logout (POST + CSRF) -->
              <li class="px-3">
                <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                  @csrf
                  <button type="submit" class="dropdown-item text-danger px-0">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                  </button>
                </form>
              </li>
            </ul>
          </div>
          @else
          <a href="{{ route('login.form') }}" class="btn btn-primary">Đăng nhập</a>
          @endauth
        </div>
      </div>
    </div>
  </div>
</nav>