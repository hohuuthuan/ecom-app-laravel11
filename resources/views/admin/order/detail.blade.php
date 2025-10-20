@extends('layouts.admin')

@section('title','Products: Chi tiết đơn hàng')

@section('body_class','order-detail-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Chi tiết đơn hàng</li>
  </ol>
</nav>

<div class="card mb-3">
  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h1 class="h4 mb-1">Đơn hàng <span id="orderCode" class="copyable"
          title="Nhấp để sao chép">#ORD-2025-000123</span></h1>
      <div class="text-muted">Tạo lúc: <span>18/10/2025 10:24</span> • Cập nhật: <span>18/10/2025 19:02</span></div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge rounded-pill text-bg-success badge-status" id="statusBadge">Đã thanh toán</span>
      <div class="dropdown no-print">
        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          Đổi trạng thái
        </button>
        <ul class="dropdown-menu dropdown-menu-end" id="statusMenu">
          <li><button class="dropdown-item" data-status="PENDING">Chờ xử lý</button></li>
          <li><button class="dropdown-item" data-status="PROCESSING">Đang xử lý</button></li>
          <li><button class="dropdown-item" data-status="SHIPPING">Đang giao</button></li>
          <li><button class="dropdown-item" data-status="DONE">Hoàn tất</button></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><button class="dropdown-item text-danger" data-status="CANCEL">Hủy</button></li>
        </ul>
      </div>
      <span class="vr d-none d-md-block"></span>
      <div class="text-end">
        <div class="mini text-muted">Tổng tiền</div>
        <div class="h5 mb-0" id="grandTotal">1.250.000&nbsp;₫</div>
      </div>
    </div>
  </div>
</div>

<!-- 3-column sections -->
<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Khách hàng</div>
        <div class="d-flex align-items-start gap-3">
          <div
            class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
            style="width:48px;height:48px">
            <i class="bi bi-person-fill" style="font-size:1.25rem"></i>
          </div>
          <div>
            <div class="fw-semibold">Nguyễn Văn A</div>
            <div class="text-muted mini">user: nguyenvana</div>
            <div class="mt-2">
              <div class="mini"><i class="bi bi-envelope"></i> <a href="mailto:a@example.com">a@example.com</a>
              </div>
              <div class="mini"><i class="bi bi-telephone"></i> +84 912 345 678</div>
            </div>
          </div>
        </div>
        <hr>
        <div class="section-title mb-2">Thanh toán</div>
        <div class="mini">Phương thức: VNPAY</div>
        <div class="mini">Mã giao dịch: <span class="copyable" id="txnId">VNP-23123123</span></div>
        <div class="mini">Trạng thái: <span class="badge text-bg-success">Paid</span></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Giao hàng</div>
        <div class="mini text-muted mb-2">Địa chỉ nhận</div>
        <div>Anh Nguyễn Văn A</div>
        <div>123 Lê Lợi, Phường Bến Thành</div>
        <div>Quận 1, TP. Hồ Chí Minh</div>
        <div>Việt Nam</div>
        <div class="mini mt-2"><i class="bi bi-telephone"></i> 0912 345 678</div>
        <hr>
        <div class="mini">Phí vận chuyển: 30.000&nbsp;₫</div>
        <div class="mini">Đơn vị VC: Giao Hàng Nhanh</div>
        <div class="mini">Mã vận đơn: <span class="copyable" id="shipCode">GHN123456789</span></div>
        <div class="mini">Dự kiến giao: 20/10/2025</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Ghi chú & thẻ</div>
        <div class="mini text-muted mb-2">Ghi chú của khách</div>
        <div class="p-2 bg-warning-soft border rounded">Vui lòng gọi trước khi giao.</div>
        <div class="mini text-muted mt-3 mb-2">Thẻ</div>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge rounded-pill text-bg-secondary">Ưu tiên</span>
          <span class="badge rounded-pill text-bg-info">Khách mới</span>
          <span class="badge rounded-pill text-bg-light border">COD-ok</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Items -->
<div class="card mt-3">
  <div class="card-body">
    <div class="section-title mb-3">Sản phẩm</div>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Sản phẩm</th>
            <th>Mã</th>
            <th class="text-center">SL</th>
            <th class="text-end">Đơn giá</th>
            <th class="text-end">Giảm</th>
            <th class="text-end">Tạm tính</th>
          </tr>
        </thead>
        <tbody id="itemBody">
          <tr>
            <td>1</td>
            <td>
              <div class="fw-semibold">Sách A: Laravel từ A-Z</div>
              <div class="text-muted mini">Bìa cứng • NXB 2024</div>
            </td>
            <td>BK-LARA-001</td>
            <td class="text-center">2</td>
            <td class="text-end">300.000&nbsp;₫</td>
            <td class="text-end">0&nbsp;₫</td>
            <td class="text-end">600.000&nbsp;₫</td>
          </tr>
          <tr>
            <td>2</td>
            <td>
              <div class="fw-semibold">Sách B: PHP nâng cao</div>
              <div class="text-muted mini">Bìa mềm • Tái bản</div>
            </td>
            <td>BK-PHP-010</td>
            <td class="text-center">1</td>
            <td class="text-end">650.000&nbsp;₫</td>
            <td class="text-end">0&nbsp;₫</td>
            <td class="text-end">650.000&nbsp;₫</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6" class="text-end">Tạm tính</td>
            <td class="text-end">1.250.000&nbsp;₫</td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Phí vận chuyển</td>
            <td class="text-end">30.000&nbsp;₫</td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Giảm giá</td>
            <td class="text-end">0&nbsp;₫</td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Tổng cộng</td>
            <td class="text-end h5 mb-0">1.280.000&nbsp;₫</td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="d-flex justify-content-end gap-2 no-print">
      <button class="btn btn-outline-secondary"><i class="bi bi-filetype-csv"></i> Xuất CSV</button>
      <button class="btn btn-outline-secondary"><i class="bi bi-receipt"></i> Hóa đơn</button>
      <button class="btn btn-outline-danger"><i class="bi bi-box-arrow-in-left"></i> Hoàn/Trả</button>
    </div>
  </div>
</div>

<!-- Timeline + Internal notes -->
<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Dòng thời gian</div>
        <div class="timeline">
          <div class="timeline-item">
            <div class="fw-semibold">Đã thanh toán</div>
            <div class="text-muted mini">18/10/2025 10:26 • VNPAY • IP 123.45.67.89</div>
          </div>
          <div class="timeline-item">
            <div class="fw-semibold">Xác nhận đơn</div>
            <div class="text-muted mini">18/10/2025 10:30 • NV: admin</div>
          </div>
          <div class="timeline-item">
            <div class="fw-semibold">Bàn giao cho vận chuyển</div>
            <div class="text-muted mini">18/10/2025 17:15 • GHN</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Ghi chú nội bộ</div>
        <div class="mb-2 mini text-muted">Chỉ hiển thị cho admin</div>
        <ul class="list-group mb-3" id="noteList">
          <li class="list-group-item d-flex justify-content-between align-items-start">
            <div class="me-2">
              Khách muốn gói kĩ.
              <div class="mini text-muted">18/10/2025 10:35 • admin</div>
            </div>
            <button class="btn btn-sm btn-outline-danger no-print"><i class="bi bi-trash"></i></button>
          </li>
        </ul>
        <div class="input-group">
          <input type="text" class="form-control" placeholder="Thêm ghi chú..." id="noteInput">
          <button class="btn btn-primary" id="noteAdd"><i class="bi bi-plus-lg"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Footer actions -->
<div class="d-flex justify-content-between align-items-center mt-3 no-print">
  <div class="mini text-muted">ID hệ thống: <span class="copyable" id="sysId">1000123</span></div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Hoàn tác</button>
    <button class="btn btn-success"><i class="bi bi-check2-circle"></i> Lưu thay đổi</button>
  </div>
</div>

  <script>
    // Fake status mapping to badge styles
    const statusMap = {
      PENDING: { text: 'Chờ xử lý', cls: 'text-bg-secondary' },
      PROCESSING: { text: 'Đang xử lý', cls: 'text-bg-warning' },
      SHIPPING: { text: 'Đang giao', cls: 'text-bg-info' },
      DONE: { text: 'Hoàn tất', cls: 'text-bg-success' },
      CANCEL: { text: 'Đã hủy', cls: 'text-bg-danger' }
    };

    // Status change UI only (no API)
    document.querySelectorAll('#statusMenu .dropdown-item').forEach(btn => {
      btn.addEventListener('click', () => {
        const k = btn.dataset.status;
        const badge = document.getElementById('statusBadge');
        const { text, cls } = statusMap[k] ?? statusMap.PENDING;
        badge.className = 'badge rounded-pill badge-status ' + cls;
        badge.textContent = (k === 'DONE' ? 'Đã hoàn tất' : text);
      });
    });

    // Copy helpers
    function copyText(el) {
      const txt = el.textContent.trim();
      navigator.clipboard?.writeText(txt);
      el.classList.add('text-success');
      setTimeout(() => el.classList.remove('text-success'), 600);
    }
    document.getElementById('orderCode').addEventListener('click', e => copyText(e.target));
    document.getElementById('txnId').addEventListener('click', e => copyText(e.target));
    document.getElementById('shipCode').addEventListener('click', e => copyText(e.target));
    document.getElementById('sysId').addEventListener('click', e => copyText(e.target));

    // Notes add/remove (UI only)
    document.getElementById('noteAdd').addEventListener('click', () => {
      const ipt = document.getElementById('noteInput');
      const v = ipt.value.trim();
      if (!v) { return; }
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-start';
      li.innerHTML = '<div class="me-2">' +
        v + '<div class="mini text-muted">' + new Date().toLocaleString('vi-VN') + ' • admin</div></div>' +
        '<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>';
      li.querySelector('button').addEventListener('click', () => li.remove());
      document.getElementById('noteList').prepend(li);
      ipt.value = '';
    });
    document.querySelectorAll('#noteList .btn-outline-danger').forEach(b => b.addEventListener('click', e => e.currentTarget.closest('li').remove()));
  </script>

@include('partials.ui.confirm-modal')
@endsection