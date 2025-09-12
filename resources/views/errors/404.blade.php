<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>404 - Không tìm thấy trang | Ecom Books</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Roboto, sans-serif;
      background: radial-gradient(1000px 500px at 20% -10%, #e9f7ef 0%, transparent 60%),
                  radial-gradient(1000px 500px at 120% 110%, #e7f0ff 0%, transparent 60%),
                  #f8f9fa;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #212529;
    }
    .error-card {
      max-width: 720px;
      width: 100%;
      background: #fff;
      border: 1px solid rgba(0,0,0,.05);
      box-shadow: 0 12px 30px rgba(0,0,0,.1);
      padding: 2.5rem 2rem;
      text-align: center;
    }
    .error-badge {
      font-size: 5rem;
      font-weight: 800;
      letter-spacing: .05em;
      color: #198754;
    }
    .error-icon svg {
      display: block;
      margin: 0 auto;
    }
    .error-search .form-control,
    .error-search .btn {
      border-radius: 0 !important;
    }
    .chips {
      margin-top: 1.25rem;
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
      justify-content: center;
    }
    .chip {
      border: 1px solid rgba(0,0,0,.15);
      padding: .35rem .75rem;
      font-size: .875rem;
      border-radius: 999px;
      background: #fff;
      color: #495057;
    }
    .btn-main {
      border-radius: 0 !important;
    }
    .logo {
      width: 150px;
      height: 150px;
    }
  </style>
</head>
<body>
  <div class="error-card">
    <div class="error-badge">404</div>
    <div class="error-icon my-3">
      <img class="logo" src="http://127.0.0.1:8000/storage/logo/e-com-book-logo.png" alt="">
    </div>
    <h1 class="fw-bold mb-2">Không tìm thấy trang</h1>
    <p class="text-muted mb-4">Có vẻ cuốn sách bạn tìm chưa có trên kệ. Hãy thử tìm kiếm hoặc quay lại trang chủ.</p>

    <!-- Nút hành động -->
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
      <a href="/" class="btn btn-primary btn-main">Về trang chủ</a>
      <a href="/login" class="btn btn-outline-secondary">Đăng nhập</a>
      <a href="/register" class="btn btn-outline-secondary">Tạo tài khoản</a>
    </div>

</body>
</html>
