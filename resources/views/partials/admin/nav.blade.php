<nav class="navbar navbar-expand navbar-light bg-white border-bottom shadow-sm px-3">
  <span class="navbar-brand mb-0 h5">Ecom Admin</span>

  <ul class="navbar-nav ms-auto">
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
        {{ auth()->user()->full_name }}
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <form action="{{ route('logout') }}" method="POST">@csrf
            <button class="dropdown-item">Đăng xuất</button>
          </form>
        </li>
      </ul>
    </li>
  </ul>
</nav>
