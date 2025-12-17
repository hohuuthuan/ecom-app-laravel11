@extends('layouts.admin')

@section('title','Quản lý tài khoản')

@section('body_class')
  @parent account-page admin-accounts-page
@endsection

@php
  $activeTab = $activeTab ?? request('tab', 'accounts');
  $validTabs = ['accounts', 'roles', 'stats'];
  if (!in_array($activeTab, $validTabs, true)) {
      $activeTab = 'accounts';
  }
@endphp

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Quản lý tài khoản</li>
  </ol>
</nav>

<div class="container-fluid admin-accounts-page">
  <div class="admin-accounts-layout">
    <div class="admin-accounts-main">
      <section
        class="account-section {{ $activeTab === 'accounts' ? 'active' : '' }}"
        data-account-section="accounts">
        <h2 class="account-section-title">
          <i class="fas fa-users me-1"></i>
          Tài khoản
        </h2>
        <p class="account-section-subtitle">Danh sách tài khoản, lọc và cập nhật trạng thái hàng loạt.</p>

        <div class="table-in-clip">
          <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Danh sách tài khoản</h5>
              <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="tab" value="accounts">
                <label class="me-2 mb-0">Hiển thị</label>
                <select class="form-select form-select-sm w-auto setupSelect2" name="per_page" onchange="this.form.submit()">
                  <option value="10" {{ request('per_page')==10?'selected':'' }}>10</option>
                  <option value="20" {{ request('per_page')==20?'selected':'' }}>20</option>
                  <option value="50" {{ request('per_page')==50?'selected':'' }}>50</option>
                </select>
              </form>
            </div>

            <div class="card-body">
              <form method="GET" class="row g-2 mb-3 filter-form select2CustomWidth">
                <input type="hidden" name="tab" value="accounts">
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
                <div class="col-md-1 d-grid">
                  <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-eraser me-1"></i>Xóa lọc
                  </a>
                </div>
              </form>

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
      </section>

      <section
        class="account-section {{ $activeTab === 'roles' ? 'active' : '' }}"
        data-account-section="roles">
        <h2 class="account-section-title">
          <i class="fas fa-user-shield me-1"></i>
          Vai trò
        </h2>
        <p class="account-section-subtitle">Danh sách vai trò và số lượng tài khoản theo từng vai trò.</p>

        <div class="table-in-clip">
          <div class="card shadow-sm">
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
      </section>

      <section
        class="account-section {{ $activeTab === 'stats' ? 'active' : '' }}"
        data-account-section="stats">
        <h2 class="account-section-title">
          <i class="fas fa-chart-pie me-1"></i>
          Thống kê
        </h2>

        @php
          $summary = is_array($accountStats ?? null) ? ($accountStats['summary'] ?? []) : [];
          $topCustomers = is_array($accountStats ?? null) ? ($accountStats['top_customers'] ?? []) : [];
          $range = is_array($accountStats ?? null) ? ($accountStats['range'] ?? []) : [];
          $fmtVnd = fn($value) => number_format((int) $value, 0, ',', '.') . 'đ';
        @endphp

        <p class="account-section-subtitle">
          Tổng quan tài khoản & Top khách hàng chi tiêu nhiều nhất ({{ $range['from'] ?? '-' }} → {{ $range['to'] ?? '-' }}).
          Doanh thu trong bảng top khách chỉ tính từ hàng hoá (đã trừ phí ship).
        </p>

        <div class="row g-3 mb-3">
          <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-muted">Tổng tài khoản</div>
                    <div class="h4 mb-0">{{ number_format((int) ($summary['total_users'] ?? 0)) }}</div>
                  </div>
                  <div class="text-muted"><i class="fas fa-users fa-lg"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-muted">Đang hoạt động</div>
                    <div class="h4 mb-0">{{ number_format((int) ($summary['active_users'] ?? 0)) }}</div>
                  </div>
                  <div class="text-success"><i class="fas fa-check-circle fa-lg"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="text-muted">Bị khoá</div>
                    <div class="h4 mb-0">{{ number_format((int) ($summary['banned_users'] ?? 0)) }}</div>
                  </div>
                  <div class="text-danger"><i class="fas fa-ban fa-lg"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">Top khách hàng (90 ngày gần nhất)</div>
              <div class="text-muted small">Chỉ tính đơn paid + completed, doanh thu đã trừ phí ship</div>
            </div>
            <span class="badge bg-light text-muted">Top {{ count($topCustomers) }}</span>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-striped mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Khách hàng</th>
                    <th class="text-end">Số đơn</th>
                    <th class="text-end">Tổng chi</th>
                    <th class="text-end">TB / đơn</th>
                    <th>Lần mua gần nhất</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($topCustomers as $i => $c)
                    <tr>
                      <td>{{ $i + 1 }}</td>
                      <td>
                        <div class="fw-semibold">{{ $c['name'] ?? 'Khách' }}</div>
                        <div class="text-muted small">
                          {{ $c['email'] ?? '' }}{{ !empty($c['phone']) ? ' • ' . $c['phone'] : '' }}
                        </div>
                      </td>
                      <td class="text-end">{{ number_format((int) ($c['orders_count'] ?? 0)) }}</td>
                      <td class="text-end">{{ $fmtVnd($c['total_spent_vnd'] ?? 0) }}</td>
                      <td class="text-end">{{ $fmtVnd($c['avg_order_vnd'] ?? 0) }}</td>
                      <td>
                        @if(!empty($c['last_order_at']))
                          {{ \Illuminate\Support\Carbon::parse($c['last_order_at'])->format('d/m/Y H:i') }}
                        @else
                          <span class="text-muted">—</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>

    <aside class="admin-accounts-nav">
      <div class="admin-accounts-nav-title">
        <i class="fas fa-layer-group"></i>
        Điều hướng
      </div>

      <ul class="admin-accounts-nav-list">
        <li class="admin-accounts-nav-item">
          <a
            href="{{ route('admin.accounts.index', array_merge(request()->query(), ['tab' => 'accounts'])) }}"
            class="admin-accounts-nav-link {{ $activeTab === 'accounts' ? 'active' : '' }}"
            data-account-target="accounts">
            <i class="fas fa-users"></i>
            <div class="nav-text">
              <span class="nav-text-main">Tài khoản</span>
              <span class="nav-text-sub">Danh sách & cập nhật</span>
            </div>
          </a>
        </li>

        <li class="admin-accounts-nav-item">
          <a
            href="{{ route('admin.accounts.index', array_merge(request()->query(), ['tab' => 'roles'])) }}"
            class="admin-accounts-nav-link {{ $activeTab === 'roles' ? 'active' : '' }}"
            data-account-target="roles">
            <i class="fas fa-user-shield"></i>
            <div class="nav-text">
              <span class="nav-text-main">Vai trò</span>
              <span class="nav-text-sub">Số lượng theo role</span>
            </div>
          </a>
        </li>

        <li class="admin-accounts-nav-item">
          <a
            href="{{ route('admin.accounts.index', array_merge(request()->query(), ['tab' => 'stats'])) }}"
            class="admin-accounts-nav-link {{ $activeTab === 'stats' ? 'active' : '' }}"
            data-account-target="stats">
            <i class="fas fa-chart-pie"></i>
            <div class="nav-text">
              <span class="nav-text-main">Thống kê</span>
              <span class="nav-text-sub">Top khách hàng</span>
            </div>
          </a>
        </li>
      </ul>
    </aside>
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

@push('scripts')
  <script id="seed"
          type="application/json"
          data-roles='@json($rolesForSelect->map(fn($r)=>["id"=>$r->id,"name"=>$r->name])->values())'
          data-avatar='@json(asset("storage/avatars/base-avatar.jpg"))'></script>
  @vite('resources/js/pages/ecom-app-laravel_admin_accounts_index.js')
@endpush
