<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
  <div class="container-fluid">
    <button id="btnCollapseDesktop" class="btn btn-primary me-2 d-none d-lg-inline-flex" type="button"
      title="Thu gọn sidebar">
      <i class="fa fa-bars"></i>
    </button>
    <button class="btn btn-primary me-2 d-inline d-lg-none" type="button" data-bs-toggle="offcanvas"
      data-bs-target="#offcanvasSidebar" title="Mở menu">
      <i class="fa fa-bars"></i>
    </button>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="topNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-2 d-none d-lg-inline">
          <span class="text-muted small">Chào mừng bạn đến với trang quản trị</span>
        </li>

        @auth
        <li class="nav-item ms-2">
          <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="nav-link btn btn-link p-0">
              <i class="fa fa-sign-out-alt"></i> Log out
            </button>
          </form>
        </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>