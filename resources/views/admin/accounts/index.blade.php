@extends('layouts.admin')

@section('title','Quản lý tài khoản')

@section('content')
{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Quản lý tài khoản</li>
  </ol>
</nav>

<div class="card shadow-sm  table-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Danh sách tài khoản</h5>
    {{-- Filter per page UI --}}
    <div class="d-flex align-items-center">
      <label class="me-2 mb-0">Hiển thị</label>
      <select class="form-select form-select-sm w-auto setupSelect2">
        <option>10</option>
        <option>20</option>
        <option>50</option>
      </select>
    </div>
  </div>

  <div class="card-body">
    {{-- Filters --}}
    <form class="row g-2 mb-3">
      <div class="col-md-3">
        <input type="text" class="form-control" placeholder="Tìm tên / email / SĐT">
      </div>
      <div class="col-md-2">
        <select class="form-select setupSelect2">
          <option value="">-- Vai trò --</option>
          <option>Admin</option>
          <option>User</option>
        </select>
      </div>
      <div class="col-md-2">
        <select class="form-select setupSelect2">
          <option value="">-- Trạng thái --</option>
          <option value="1">Bình thường</option>
          <option value="0">Bị khoá</option>
        </select>
      </div>
      <div class="col-md-2">
        <input type="number" class="form-control" placeholder="Chi tiêu từ">
      </div>
      <div class="col-md-2">
        <input type="number" class="form-control" placeholder="Chi tiêu đến">
      </div>
      <div class="col-md-1 d-grid">
        <button type="button" class="btn btn-primary">Lọc</button>
      </div>
    </form>

    {{-- Bulk update --}}
    <div class="d-flex justify-content-between mb-2">
      <div class="d-flex gap-2">
        <select class="form-select form-select-sm w-auto setupSelect2" id="bulk_status">
          <option>-- Cập nhật trạng thái --</option>
          <option value="1">Kích hoạt</option>
          <option value="0">Khoá</option>
        </select>
        <button type="button" class="btn btn-sm btn-primary" id="btnBulkOpen">Cập nhật</button>
      </div>

    </div>

    {{-- Table --}}
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th><input type="checkbox" id="check_all"></th>
            <th>STT</th>
            <th>Ảnh</th>
            <th>Tên</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Vai trò</th>
            <th>Tổng chi tiêu</th>
            <th>Trạng thái</th>
            <th class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @for ($i = 1; $i <= 10; $i++)
            <tr>
            <td><input type="checkbox" class="row-checkbox"></td>
            <td>{{ $i }}</td>
            <td><img src="https://via.placeholder.com/60" class="img-thumbnail"></td>
            <td>Người dùng {{ $i }}</td>
            <td>user{{ $i }}@mail.com</td>
            <td>0900{{ 1000 + $i }}</td>
            <td>{{ $i % 3 ? 'User' : 'Admin' }}</td>
            <td>{{ number_format($i * 1000000, 0, ',', '.') }} VNĐ</td>
            <td>
              @if($i % 2)
              <span class="badge bg-success">Bình thường</span>
              @else
              <span class="badge bg-danger">Bị khoá</span>
              @endif
            </td>
            <td class="text-center">
              <a href="#" class="btn btn-sm btn-success"><i class="fa fa-edit"></i></a>
            </td>
            </tr>
            @endfor
        </tbody>
      </table>
    </div>

    {{-- Pagination giả lập --}}
    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
      </ul>
    </nav>
  </div>
</div>

@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const table = document.querySelector('.table.table-bordered.table-striped') || document.querySelector('table');
    const master = document.getElementById('check_all');
    const btnOpen = document.getElementById('btnBulkOpen');
    const select = document.getElementById('bulk_status');


    const getRowCheckboxes = () => table ? table.querySelectorAll('tbody .row-checkbox') : [];

    const markRow = (cb) => {
      const tr = cb.closest('tr');
      if (!tr) return;
      tr.classList.toggle('row-checked', cb.checked);
      tr.classList.remove('table-active');
    };

    const refreshMaster = () => {
      const cbs = getRowCheckboxes();
      const total = cbs.length;
      const checked = [...cbs].filter(cb => cb.checked).length;

      if (!master) return;
      if (total === 0) {
        master.checked = false;
        master.indeterminate = false;
        return;
      }

      master.checked = (checked > 0 && checked === total);
      master.indeterminate = false;
    };

    const getCheckedCount = () => {
      const cbs = getRowCheckboxes();
      return [...cbs].filter(cb => cb.checked).length;
    };

    const statusText = (val) => {
      if (val === '1') return 'Kích hoạt';
      if (val === '0') return 'Khoá';
      return '—';
    };

    if (table) {
      table.addEventListener('change', (e) => {
        const t = e.target;
        if (!t.classList.contains('row-checkbox')) return;
        markRow(t);
        refreshMaster();
      });
    }

    if (table) {
      table.addEventListener('click', (e) => {
        const td = e.target.closest('td');
        if (!td) return;

        if (td.cellIndex !== 0) return;

        if (e.target.tagName === 'INPUT') return;

        const cb = td.querySelector('.row-checkbox');
        if (!cb) return;

        cb.checked = !cb.checked;
        markRow(cb);
        refreshMaster();
      });
    }
    if (master) {
      master.addEventListener('change', () => {
        const cbs = getRowCheckboxes();
        cbs.forEach(cb => {
          cb.checked = master.checked;
          markRow(cb);
        });
        refreshMaster();
      });
    }

    getRowCheckboxes().forEach(markRow);
    refreshMaster();

    // ====== 5) Bulk update confirm (giữ logic của bạn) ======
    if (btnOpen) {
      btnOpen.addEventListener('click', async () => {
        const count = getCheckedCount();
        const val = select?.value;

        if (!count) {
          return UIConfirm({
            title: 'Thiếu lựa chọn',
            message: 'Vui lòng chọn ít nhất <b>1</b> tài khoản trước khi cập nhật.',
            confirmText: 'Đã hiểu',
            cancelText: 'Đóng',
            size: 'sm'
          });
        }
        if (val !== '1' && val !== '0') {
          return UIConfirm({
            title: 'Chưa chọn trạng thái',
            message: 'Vui lòng chọn trạng thái đích cần cập nhật.',
            confirmText: 'Đã hiểu',
            cancelText: 'Đóng',
            size: 'sm'
          });
        }

        const target = statusText(val);
        const html = `
        Bạn sắp cập nhật trạng thái cho <b>${count}</b> tài khoản.<br>
        Trạng thái mới: <span class="badge ${val==='1'?'bg-success':'bg-danger'}">${target}</span>
      `;

        const ok = await UIConfirm({
          title: 'Xác nhận cập nhật',
          message: html,
          confirmText: 'Xác nhận',
          cancelText: 'Huỷ',
          size: 'md'
        });

        if (ok) {
          // document.getElementById('bulkForm').submit();
          console.log('User confirmed bulk update to:', val);
        }
      });
    }
  });
</script>
@endpush