@extends('layouts.admin')

@section('title','Quản lý tài khoản')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Quản lý tài khoản</li>
  </ol>
</nav>

{{-- Tabs Bootstrap --}}
<ul class="nav nav-tabs mb-3" id="accountTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="accounts-tab" data-bs-toggle="tab" data-bs-target="#accounts-pane" type="button" role="tab">
      Tài khoản
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-pane" type="button" role="tab">
      Vai trò
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats-pane" type="button" role="tab">
      Thống kê
    </button>
  </li>
</ul>

<div class="tab-content" id="accountTabsContent">
  {{-- ================== TAB 1 ================== --}}
  <div class="tab-pane fade show active" id="accounts-pane" role="tabpanel" aria-labelledby="accounts-tab">
    <div class="table-in-clip">
      <div class="card shadow-sm table-in">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Danh sách tài khoản</h5>
          <form method="GET" class="d-flex align-items-center">
            <label class="me-2 mb-0">Hiển thị</label>
            <select class="form-select form-select-sm w-auto setupSelect2" name="per_page" onchange="this.form.submit()">
              <option value="10" {{ request('per_page')==10?'selected':'' }}>10</option>
              <option value="20" {{ request('per_page')==20?'selected':'' }}>20</option>
              <option value="50" {{ request('per_page')==50?'selected':'' }}>50</option>
            </select>
          </form>
        </div>

        <div class="card-body">
          {{-- Filters --}}
          <form method="GET" class="row g-2 mb-3 filter-form">
            <div class="col-md-3">
              <input type="text" name="keyword" class="form-control" placeholder="Tìm tên / email / SĐT" value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
              <select name="role_id" class="form-select setupSelect2">
                <option value="">-- Tất cả vai trò --</option>
                @foreach($rolesForSelect as $role)
                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                  {{ $role->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <select name="status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('status')==='ACTIVE'?'selected':'' }}>Bình thường</option>
                <option value="BAN" {{ request('status')==='BAN'?'selected':'' }}>Bị khoá</option>
              </select>
            </div>
            <div class="col-md-1 d-grid">
              <button type="submit" class="btn btn-primary btn-admin">Lọc</button>
            </div>
          </form>

          {{-- Bulk update --}}
          <div class="d-flex justify-content-between mb-2">
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm w-auto setupSelect2" id="bulk_status">
                <option value="">-- Cập nhật trạng thái --</option>
                <option value="ACTIVE">Kích hoạt</option>
                <option value="BAN">Khoá</option>
              </select>
              <button type="button" class="btn btn-sm btn-primary btn-admin" id="btnBulkOpen">Cập nhật</button>
            </div>
          </div>

          {{-- Table --}}
          <div class="table-responsive">
            <table id="accountTable" class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th><input type="checkbox" id="check_all"></th>
                  <th>#</th>
                  <th>Tên</th>
                  <th>Email</th>
                  <th>SĐT</th>
                  <th>Vai trò</th>
                  <th>Trạng thái</th>
                  <th class="text-center user-action">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($users as $index => $user)
                <tr>
                  <td><input type="checkbox" class="row-checkbox" value="{{ $user->id }}"></td>
                  <td>{{ $users->firstItem() + $index }}</td>
                  <td>{{ $user->full_name }}</td>
                  <td>{{ $user->email }}</td>
                  <td>{{ $user->phone ?? '-' }}</td>
                  <td>
                    @if($user->roles->isNotEmpty())
                    {{ $user->roles->pluck('name')->join(', ') }}
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>

                  <td>
                    @if($user->status == 'ACTIVE')
                    <span class="badge bg-success">Bình thường</span>
                    @else
                    <span class="badge bg-danger">Bị khoá</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <button type="button"
                      class="btn btn-sm btn-success btnAccountEdit"
                      data-update-url="{{ route('admin.accounts.update', $user->id) }}"
                      data-full_name="{{ $user->full_name }}"
                      data-email="{{ $user->email }}"
                      data-phone="{{ $user->phone }}"
                      data-address="{{ $user->address }}"
                      data-status="{{ $user->status }}"
                      data-avatar="{{ $user->avatar ? Storage::url($user->avatar) : Storage::url('avatars/base-avatar.jpg') }}"
                      data-role_ids="{{ $user->roles->pluck('id')->join(',') }}">
                      <i class="fa fa-edit"></i>
                    </button>
                  </td>

                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $users->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ================== TAB 2 ================== --}}
  <div class="tab-pane fade" id="roles-pane" role="tabpanel" aria-labelledby="roles-tab">
    <div class="table-in-clip">
      <div class="card shadow-sm table-in">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Danh sách vai trò</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width:70px">#</th>
                  <th>Tên vai trò</th>
                  <th>Mô tả</th>
                  <th class="text-center" style="width:180px">Số lượng tài khoản</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rolesSummary as $i => $role)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td>{{ $role->name }}</td>
                  <td>{{ $role->description ?? '-' }}</td>
                  <td class="text-center">
                    <span class="badge bg-secondary">{{ $role->users_count }}</span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ================== TAB 3: THỐNG KÊ (HTML tĩnh) ================== --}}
  <div class="tab-pane fade" id="stats-pane" role="tabpanel" aria-labelledby="stats-tab">
    <div class="table-in-clip">
      <div class="card shadow-sm table-in">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Thống kê</h5>
        </div>

        <div class="card-body">
          <div class="row g-3">
            {{-- Cột trái: Bảng Top 10 tài khoản chi tiêu nhiều nhất (tĩnh) --}}
            <div class="col-lg-8">
              <div class="card h-100">
                <div class="card-header bg-white">
                  <strong>Top 10 tài khoản chi tiêu nhiều nhất</strong>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle">
                      <thead class="table-light">
                        <tr>
                          <th style="width:60px">#</th>
                          <th>Họ tên</th>
                          <th>Email</th>
                          <th class="text-end" style="width:160px">Tổng chi tiêu</th>
                          <th class="text-center" style="width:100px">Số đơn</th>
                          <th style="width:160px">Lần cuối mua</th>
                        </tr>
                      </thead>
                      <tbody>
                        {{-- DỮ LIỆU TĨNH MẪU – sau này fill thật bằng server/JS --}}
                        <tr>
                          <td>1</td>
                          <td>Nguyễn Văn A</td>
                          <td>a.nguyen@example.com</td>
                          <td class="text-end">125.000.000₫</td>
                          <td class="text-center">18</td>
                          <td>01/09/2025</td>
                        </tr>
                        <tr>
                          <td>2</td>
                          <td>Trần Thị B</td>
                          <td>b.tran@example.com</td>
                          <td class="text-end">98.500.000₫</td>
                          <td class="text-center">12</td>
                          <td>30/08/2025</td>
                        </tr>
                        <tr>
                          <td>3</td>
                          <td>Lê Minh C</td>
                          <td>c.le@example.com</td>
                          <td class="text-end">87.300.000₫</td>
                          <td class="text-center">10</td>
                          <td>28/08/2025</td>
                        </tr>
                        <tr>
                          <td>4</td>
                          <td>Phạm D</td>
                          <td>d.pham@example.com</td>
                          <td class="text-end">76.900.000₫</td>
                          <td class="text-center">9</td>
                          <td>27/08/2025</td>
                        </tr>
                        <tr>
                          <td>5</td>
                          <td>Võ E</td>
                          <td>e.vo@example.com</td>
                          <td class="text-end">71.200.000₫</td>
                          <td class="text-center">11</td>
                          <td>25/08/2025</td>
                        </tr>
                        <tr>
                          <td>6</td>
                          <td>Đỗ F</td>
                          <td>f.do@example.com</td>
                          <td class="text-end">66.800.000₫</td>
                          <td class="text-center">8</td>
                          <td>24/08/2025</td>
                        </tr>
                        <tr>
                          <td>7</td>
                          <td>Bùi G</td>
                          <td>g.bui@example.com</td>
                          <td class="text-end">59.400.000₫</td>
                          <td class="text-center">7</td>
                          <td>23/08/2025</td>
                        </tr>
                        <tr>
                          <td>8</td>
                          <td>Huỳnh H</td>
                          <td>h.huynh@example.com</td>
                          <td class="text-end">52.000.000₫</td>
                          <td class="text-center">6</td>
                          <td>22/08/2025</td>
                        </tr>
                        <tr>
                          <td>9</td>
                          <td>Đặng I</td>
                          <td>i.dang@example.com</td>
                          <td class="text-end">47.600.000₫</td>
                          <td class="text-center">6</td>
                          <td>20/08/2025</td>
                        </tr>
                        <tr>
                          <td>10</td>
                          <td>Trịnh K</td>
                          <td>k.trinh@example.com</td>
                          <td class="text-end">41.300.000₫</td>
                          <td class="text-center">5</td>
                          <td>18/08/2025</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="small text-muted">* Dữ liệu mẫu, chỉ để minh hoạ giao diện.</div>
                </div>
              </div>
            </div>

            {{-- Cột phải: “Sơ đồ” – thuần HTML/CSS (không JS) --}}
            <div class="col-lg-4">
              {{-- Biểu đồ thanh “giả lập” bằng progress bar --}}
              <div class="card mb-3">
                <div class="card-header bg-white">
                  <strong>Top chi tiêu (minh hoạ)</strong>
                </div>
                <div class="card-body">
                  <ul class="list-unstyled mb-0 stats-bars">
                    <li class="mb-3">
                      <div class="d-flex justify-content-between small mb-1">
                        <span>Nguyễn Văn A</span><span>125tr</span>
                      </div>
                      <div class="progress" style="height:8px;">
                        <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                      </div>
                    </li>
                    <li class="mb-3">
                      <div class="d-flex justify-content-between small mb-1">
                        <span>Trần Thị B</span><span>98.5tr</span>
                      </div>
                      <div class="progress" style="height:8px;">
                        <div class="progress-bar" role="progressbar" style="width: 79%"></div>
                      </div>
                    </li>
                    <li class="mb-3">
                      <div class="d-flex justify-content-between small mb-1">
                        <span>Lê Minh C</span><span>87.3tr</span>
                      </div>
                      <div class="progress" style="height:8px;">
                        <div class="progress-bar" role="progressbar" style="width: 70%"></div>
                      </div>
                    </li>
                    <li>
                      <div class="d-flex justify-content-between small mb-1">
                        <span>Phạm D</span><span>76.9tr</span>
                      </div>
                      <div class="progress" style="height:8px;">
                        <div class="progress-bar" role="progressbar" style="width: 61%"></div>
                      </div>
                    </li>
                  </ul>
                  <div class="small text-muted mt-2">* Tỉ lệ thanh dựa trên người cao nhất (=100%).</div>
                </div>
              </div>

              {{-- Donut chart “giả lập” bằng CSS thuần (conic-gradient) --}}
              <div class="card">
                <div class="card-header bg-white">
                  <strong>Tỷ lệ chi tiêu theo vai trò (minh hoạ)</strong>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                  <div class="stats-donut mb-2"></div>
                  <ul class="list-unstyled small mb-0">
                    <li class="d-flex align-items-center gap-2">
                      <span class="legend-dot legend-admin"></span> Admin ~ 40%
                    </li>
                    <li class="d-flex align-items-center gap-2">
                      <span class="legend-dot legend-customer"></span> Customer ~ 35%
                    </li>
                    <li class="d-flex align-items-center gap-2">
                      <span class="legend-dot legend-editor"></span> Editor ~ 25%
                    </li>
                  </ul>
                  <div class="small text-muted mt-2">* Chỉ là số liệu mẫu.</div>
                </div>
              </div>
            </div>
          </div> <!-- /.row -->
        </div> <!-- /.card-body -->
      </div>
    </div>
  </div>
</div>

<form id="bulkForm" method="POST" action="{{ route('admin.accounts.bulk-update') }}" class="d-none">
  @csrf
  <input type="hidden" name="status" id="bulk_status_input">
  <div id="bulk_ids_container"></div>
</form>

@include('partials.ui.account.confirm-modal')
@include('partials.ui.account.account-edit-modal')
@endsection

@push('scripts')
<script>
  // @ts-nocheck
  document.addEventListener('DOMContentLoaded', function() {
    // ===== Refs
    const table = document.getElementById('accountTable');
    const master = document.getElementById('check_all');
    const btnOpen = document.getElementById('btnBulkOpen');
    const select = document.getElementById('bulk_status');

    const bulkForm = document.getElementById('bulkForm');
    const bulkStatusInput = document.getElementById('bulk_status_input');
    const bulkIdsContainer = document.getElementById('bulk_ids_container');

    const modalEl = document.getElementById('uiAccountEditModal');
    const formEl = document.getElementById('uiAccountEditForm');
    const imgPrev = document.getElementById('ac_avatar_preview');
    const fileInput = document.getElementById('ac_avatar');
    const statusSel = document.getElementById('ac_status');

    const tokensBox = document.getElementById('ac_roles_tokens');
    const suggestEl = document.getElementById('ac_roles_suggest');
    const hiddenBox = document.getElementById('ac_roles_inputs');

    const ROLES = Array.isArray(window.ROLES_MASTER) ? window.ROLES_MASTER.map(r => ({
      id: String(r.id),
      name: r.name
    })) : [];
    const AVATAR_PH = typeof window.AVATAR_BASE === 'string' ? window.AVATAR_BASE : '';

    // ===== Helpers: table
    function getRowCheckboxes() {
      return table ? Array.from(table.querySelectorAll('tbody .row-checkbox')) : [];
    }

    function markRow(cb) {
      const tr = cb ? cb.closest('tr') : null;
      if (!tr) return;
      if (cb.checked) tr.classList.add('row-checked');
      else tr.classList.remove('row-checked');
      tr.classList.remove('table-active');
    }

    function refreshMaster() {
      if (!master) return;
      const cbs = getRowCheckboxes();
      const total = cbs.length;
      const checked = cbs.filter(x => x.checked).length;
      master.indeterminate = false;
      master.checked = total > 0 && checked === total;
    }

    function getCheckedIds() {
      return getRowCheckboxes().filter(x => x.checked).map(x => x.value);
    }

    function statusText(v) {
      if (v === 'ACTIVE') return 'Kích hoạt';
      if (v === 'BAN') return 'Khoá';
      return '—';
    }

    function normStatus(s) {
      s = String(s || '').toUpperCase().trim();
      return s === 'BAN' ? 'BAN' : 'ACTIVE';
    }

    // ===== Events: table
    if (table) {
      table.addEventListener('change', function(e) {
        const t = e.target;
        if (!t.classList || !t.classList.contains('row-checkbox')) return;
        markRow(t);
        refreshMaster();
      });
      table.addEventListener('click', function(e) {
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
      master.addEventListener('change', function() {
        const cbs = getRowCheckboxes();
        for (const cb of cbs) {
          cb.checked = master.checked;
          markRow(cb);
        }
        master.indeterminate = false;
        refreshMaster();
      });
    }
    getRowCheckboxes().forEach(markRow);
    refreshMaster();

    // ===== Confirm fallback
    function stripHtml(html) {
      const d = document.createElement('div');
      d.innerHTML = html;
      return d.textContent || d.innerText || '';
    }
    async function confirmDialog(opts) {
      if (typeof window.UIConfirm === 'function') return await window.UIConfirm(opts);
      const title = (opts && opts.title) || 'Xác nhận';
      const msg = (opts && opts.message) || 'Bạn có chắc không?';
      return window.confirm(title + '\n\n' + stripHtml(msg));
    }

    // ===== Bulk submit
    if (btnOpen) {
      btnOpen.addEventListener('click', async function() {
        const ids = getCheckedIds();
        const val = select && select.value ? select.value : '';

        if (!ids.length) {
          await confirmDialog({
            title: 'Thiếu lựa chọn',
            message: 'Vui lòng chọn ít nhất <b>1</b> tài khoản.'
          });
          return;
        }
        if (val !== 'ACTIVE' && val !== 'BAN') {
          await confirmDialog({
            title: 'Chưa chọn trạng thái',
            message: 'Vui lòng chọn trạng thái đích.'
          });
          return;
        }

        const ok = await confirmDialog({
          title: 'Xác nhận cập nhật',
          message: 'Bạn sắp cập nhật cho <b>' + ids.length + '</b> tài khoản.<br>Trạng thái: <span class="badge ' + (val === 'ACTIVE' ? 'bg-success' : 'bg-danger') + '">' + statusText(val) + '</span>',
          confirmText: 'Xác nhận',
          cancelText: 'Huỷ'
        });
        if (!ok) return;

        if (!bulkForm) return;
        bulkStatusInput.value = val;
        bulkIdsContainer.innerHTML = '';
        for (const id of ids) {
          const i = document.createElement('input');
          i.type = 'hidden';
          i.name = 'ids[]';
          i.value = id;
          bulkIdsContainer.appendChild(i);
        }
        bulkForm.submit();
      });
    }

    // ===== Avatar preview
    if (fileInput && imgPrev) {
      fileInput.addEventListener('change', function() {
        const f = fileInput.files && fileInput.files[0];
        if (!f) return;
        imgPrev.src = URL.createObjectURL(f);
      });
    }

    // ===== Roles pick-only
    let selected = [];

    function setSelected(ids) {
      selected = Array.from(new Set((Array.isArray(ids) ? ids : []).filter(Boolean).map(String)));
      renderRoles();
    }

    function addById(id) {
      id = String(id);
      if (!id || selected.includes(id)) return;
      selected.push(id);
      renderRoles();
    }

    function removeById(id) {
      id = String(id);
      selected = selected.filter(x => x !== id);
      renderRoles();
    }

    function remainingRoles() {
      return ROLES.filter(r => !selected.includes(String(r.id)));
    }

    function renderRoles() {
      if (!tokensBox || !suggestEl || !hiddenBox) return;

      hiddenBox.innerHTML = '';
      for (const id of selected) {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = 'role_ids[]';
        i.value = id;
        hiddenBox.appendChild(i);
      }

      const tokens = selected.map(id => {
        const role = ROLES.find(r => String(r.id) === String(id));
        if (!role) return '';
        return '<span class="ac-tag" data-id="' + id + '">' +
          '<span>' + role.name + '</span>' +
          '<button class="ac-x" type="button" aria-label="Xóa" data-x="' + id + '">×</button>' +
          '</span>';
      }).join('');
      tokensBox.innerHTML = tokens;
      tokensBox.classList.toggle('is-empty', selected.length === 0);

      const list = remainingRoles();
      suggestEl.innerHTML = list.map(r => '<span class="sg" data-add="' + r.id + '"><span class="plus">＋</span><span>' + r.name + '</span></span>').join('');
    }
    if (tokensBox) {
      tokensBox.addEventListener('click', function(e) {
        const id = e.target && e.target.getAttribute && e.target.getAttribute('data-x');
        if (id) removeById(id);
      });
    }
    if (suggestEl) {
      suggestEl.addEventListener('click', function(e) {
        const chip = e.target.closest && e.target.closest('[data-add]');
        if (!chip) return;
        addById(chip.getAttribute('data-add'));
      });
    }

    // ===== Fill form + open modal
    function fillFormFromBtn(btn) {
      if (!btn || !formEl) return;

      const url = btn.getAttribute('data-update-url') || '';
      const fullName = btn.getAttribute('data-full_name') || '';
      const email = btn.getAttribute('data-email') || '';
      const phone = btn.getAttribute('data-phone') || '';
      const address = btn.getAttribute('data-address') || '';
      const status = normStatus(btn.getAttribute('data-status'));
      const avatar = btn.getAttribute('data-avatar') || '';
      const roleIdsCS = (btn.getAttribute('data-role_ids') || '').trim();
      const roleIds = roleIdsCS ? roleIdsCS.split(',').map(s => s.trim()).filter(Boolean) : [];

      formEl.action = url;
      const nameEl = formEl.querySelector('#ac_full_name');
      const mailEl = formEl.querySelector('#ac_email');
      const phEl = formEl.querySelector('#ac_phone');
      const addrEl = formEl.querySelector('#ac_address');
      if (nameEl) nameEl.value = fullName;
      if (mailEl) mailEl.value = email;
      if (phEl) phEl.value = phone;
      if (addrEl) addrEl.value = address;

      if (imgPrev) imgPrev.src = avatar || AVATAR_PH;
      if (fileInput) {
        try {
          fileInput.value = '';
        } catch (_) {}
      }

      if (statusSel) {
        for (const o of statusSel.options) {
          if (!o.value) o.selected = false;
        }
        statusSel.value = status;
        if (window.jQuery && $.fn && $.fn.select2) $('#ac_status').trigger('change.select2');
      }

      setSelected(roleIds);
    }

    function openModal(el) {
      if (!el) return;
      if (window.bootstrap && bootstrap.Modal) {
        const inst = bootstrap.Modal.getOrCreateInstance(el);
        inst.show();
      }
    }
    document.addEventListener('click', function(e) {
      const btn = e.target.closest ? e.target.closest('.btnAccountEdit') : null;
      if (!btn) return;
      fillFormFromBtn(btn);
      openModal(modalEl);
    });

    // initial render for roles box
    renderRoles();
  });
</script>

<script>
function getTabFromURL(){
  const u = new URL(location.href);
  const t = u.searchParams.get('tab');
  return ['accounts','roles','stats'].includes(t) ? t : 'accounts';
}
function setTabInURL(tab, replace=false){
  const u = new URL(location.href);
  u.searchParams.set('tab', tab);
  replace ? history.replaceState(null,'',u) : history.pushState(null,'',u);
}
function showTab(tab){
  const trigger = document.querySelector(`[data-bs-target="#${tab}-pane"]`);
  if (!trigger) return;
  new bootstrap.Tab(trigger).show();
}

document.addEventListener('DOMContentLoaded', () => {
  // mở đúng tab theo URL (mặc định 'accounts')
  showTab(getTabFromURL());

  // khi đổi tab thì cập nhật ?tab=
  const tabs = document.getElementById('accountTabs');
  if (tabs) {
    tabs.addEventListener('shown.bs.tab', (e) => {
      const pane = e.target.getAttribute('data-bs-target'); // '#roles-pane' ...
      let tab = 'accounts';
      if (pane.includes('roles')) tab = 'roles';
      else if (pane.includes('stats')) tab = 'stats';
      setTabInURL(tab);
    });
  }

  // hỗ trợ nút Back/Forward
  window.addEventListener('popstate', () => showTab(getTabFromURL()));

  // lần đầu: nếu chưa có ?tab= thì set theo tab hiện tại (không đẩy history)
  setTabInURL(getTabFromURL(), true);
});
</script>

@endpush

@push('scripts')
<script id="seed"
  data-roles='@json($rolesForSelect->map(fn($r)=>["id"=>(string)$r->id,"name"=>$r->name])->values())'
  data-avatar='@json(Storage::url("user/base-avatar.jpg"))'>
</script>

<script>
  const el = document.getElementById('seed');
  window.ROLES_MASTER = JSON.parse(el.dataset.roles);
  window.AVATAR_BASE = JSON.parse(el.dataset.avatar);
</script>
@endpush