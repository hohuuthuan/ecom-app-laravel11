<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin</a>
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#topbar-nav">
        <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
      </button>
    </div>
    <div class="collapse navbar-collapse" id="topbar-nav">
      <ul class="nav navbar-nav navbar-right">
        @auth
          <li>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
              @csrf
              <button class="btn btn-link navbar-btn">Logout</button>
            </form>
          </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>
<div style="height:50px"></div>
