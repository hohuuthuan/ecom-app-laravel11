<aside class="sidebar">
  <ul class="nav nav-pills nav-stacked">
    <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    </li>
    <li><a href="#">Users</a></li>
    <li><a href="#">Products</a></li>
    <li><a href="#">Orders</a></li>
  </ul>
</aside>
