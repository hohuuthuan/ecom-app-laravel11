@extends('layouts.admin')

@section('title','Bảng điều khiển')

@section('content')
<div class="card">
  <div class="card-header">Demo Select2 + Cards</div>
  <div class="card-body">
    <form class="space-y-4">
      <div>
        <label class="form-label">Danh mục</label>
        <select class="form-select setupSelect2" data-allow-clear="true" placeholder="Chọn danh mục">
          <option></option>
          <option value="1">Sách</option>
          <option value="2">Văn phòng phẩm</option>
          <option value="3">Thiết bị</option>
        </select>
      </div>

      <div class="flex gap-2">
        <button type="button" class="btn btn-primary" onclick="document.dispatchEvent(new CustomEvent('demo:toast'))">Hiện toast</button>
        <button type="button" class="btn btn-danger" onclick="window.__confirm && window.__confirm()">Xác nhận</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('demo:toast', () => {
    const box = document.createElement('div');
    box.setAttribute('data-toast','success');
    box.setAttribute('data-autohide','true');
    box.setAttribute('data-delay-ms','2500');
    box.textContent = 'Thành công';
    document.body.appendChild(box);
  });
  window.__confirm = () => {
    document.getElementById('uiConfirmMessage').textContent = 'Bạn muốn xoá bản ghi này?';
    document.getElementById('uiConfirmModal').dataset.open = '1';
  };
</script>
@endpush
@endsection
