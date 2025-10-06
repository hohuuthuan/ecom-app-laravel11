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
                    <a class="nav-link active" href="#home">Trang chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Danh mục
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-heart me-2"></i>Tiểu thuyết</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-briefcase me-2"></i>Kinh doanh</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-brain me-2"></i>Tâm lý</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-child me-2"></i>Thiếu nhi</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#bestsellers">Bán chạy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">Liên hệ</a>
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
                    <a href="#" class="text-decoration-none me-3 position-relative" onclick="toggleWishlist()">
                        <i class="fas fa-heart text-danger" style="font-size: 1.2rem;"></i>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"
                            id="wishlistCount" style="font-size: 0.7rem;">0</span>
                    </a>

                    <!-- Cart -->
                    <a href="#" class="text-decoration-none me-3 position-relative" onclick="showCart()">
                        <i class="fas fa-shopping-cart text-primary" style="font-size: 1.2rem;"></i>
                        <span class="badge bg-primary position-absolute top-0 start-100 translate-middle"
                            id="headerCartBadge" style="font-size: 0.7rem;">0</span>
                    </a>

                    @auth
                    <!-- User Avatar Dropdown -->
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-decoration-none d-flex align-items-center"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            @if(auth()->user()->avatar)
                            <img
                                src="{{ Storage::url(auth()->user()->avatar) }}"
                                alt="Avatar {{ auth()->user()->name ?? auth()->user()->username }}"
                                class="rounded-circle me-2"
                                style="width:35px;height:35px;object-fit:cover;">
                            @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                style="width:35px;height:35px;">
                                <i class="fas fa-user text-white" style="font-size:0.9rem;"></i>
                            </div>
                            @endif

                            <span class="text-dark fw-medium d-none d-md-inline">
                                {{ auth()->user()->name ?? auth()->user()->username }}
                            </span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <div class="dropdown-header">
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->avatar)
                                        <img
                                            src="{{ Storage::url(auth()->user()->avatar) }}"
                                            alt="Avatar {{ auth()->user()->name ?? auth()->user()->username }}"
                                            class="rounded-circle me-2"
                                            style="width:40px;height:40px;object-fit:cover;">
                                        @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                            style="width:40px;height:40px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">
                                                {{ auth()->user()->name ?? auth()->user()->username }}
                                            </div>
                                            <small class="text-muted">{{ auth()->user()->email }}</small>
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
                                <a class="dropdown-item" href="#" onclick="showProfile()">
                                    <i class="fas fa-user me-2"></i>Tài khoản của tôi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showProfileTab('orders')">
                                    <i class="fas fa-shopping-bag me-2"></i>Đơn hàng của tôi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showProfileTab('wishlist')">
                                    <i class="fas fa-heart me-2"></i>Sách yêu thích
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showProfileTab('settings')">
                                    <i class="fas fa-cog me-2"></i>Cài đặt
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