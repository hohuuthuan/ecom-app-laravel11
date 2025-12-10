@extends('layouts.admin')

@section('title','Quản lý tài khoản')

@section('body_class','account-page')

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
          <form method="GET" class="row g-2 mb-3 filter-form select2CustomWidth">
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
                <option value="BAN"    {{ request('status')==='BAN'?'selected':'' }}>Bị khoá</option>
              </select>
            </div>
            <div class="col-md-1 d-grid">
              <button type="submit" class="btn-admin">Lọc</button>
            </div>
          </form>

          {{-- Bulk update --}}
          <div class="d-flex justify-content-between mb-2 select2CustomWidth">
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm w-auto setupSelect2" id="bulk_status">
                <option value="">-- Cập nhật trạng thái --</option>
                <option value="ACTIVE">Kích hoạt</option>
                <option value="BAN">Khoá</option>
              </select>
              <button type="button" class="btn-admin" id="btnBulkOpen">Cập nhật</button>
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
                    <td>{{ $user->name }}</td>
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
                        data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                        data-phone="{{ $user->phone }}"
                        data-address="{{ $user->address }}"
                        data-status="{{ $user->status }}"
                        data-avatar="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('storage/avatars/base-avatar.jpg') }}"
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
                    <td class="text-center"><span class="badge bg-secondary">{{ $role->users_count }}</span></td>
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
          {{-- … giữ nguyên nội dung thống kê tĩnh như hiện tại … --}}
        </div>
      </div>
    </div>
  </div>
</div>

<form id="bulkForm" method="POST" action="{{ route('admin.accounts.bulk-update') }}" class="d-none">
  @csrf
  <input type="hidden" name="status" id="bulk_status_input">
  <div id="bulk_ids_container"></div>
</form>

@include('partials.ui.confirm-modal')
@include('partials.ui.account.account-edit-modal')
@endsection

{{-- ===== Seed cho JS: roles + avatar mặc định ===== --}}
@push('scripts')
  <script id="seed"
          type="application/json"
          data-roles='@json($rolesForSelect->map(fn($r)=>["id"=>$r->id,"name"=>$r->name])->values())'
          data-avatar='@json(asset("storage/avatars/base-avatar.jpg"))'></script>
  @vite('resources/js/pages/ecom-app-laravel_admin_accounts_index.js')
@endpush
