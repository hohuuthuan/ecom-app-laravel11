{{-- Sidebar mặc định (tuỳ chỉnh theo nhu cầu) --}}
<div class="card shadow-sm">
  <div class="card-header fw-semibold">Menu</div>
  <ul class="list-group list-group-flush">
    <li class="list-group-item">
      <a class="text-decoration-none" href="{{ route('home') }}">
        <i class="bi bi-house-door"></i> Trang chủ
      </a>
    </li>
    @auth
      <li class="list-group-item">
        <a class="text-decoration-none" href="{{ route('dashboard') }}">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
      </li>
    @endauth
  </ul>
</div>
