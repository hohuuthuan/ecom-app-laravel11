@extends('layouts.user')
@section('title','Quản lý hồ sơ')

@section('content')
@php
$activeTab = request('tab', 'info');
$validTabs = ['info', 'orders', 'addresses', 'password'];
if (!in_array($activeTab, $validTabs, true)) {
$activeTab = 'info';
}

$pp = (int) request('per_page_order', 10);
@endphp

<div class="container profile-overview-page">
    <div class="page-header">
        <div class="page-nav">
            <a class="btn continue-shopping" href="{{ route('home') }}">
                <i class="bi bi-arrow-left"></i>
                Quay lại trang chủ
            </a>
        </div>
        <h1 class="pageTitle">HỒ SƠ CỦA BẠN </h1>
    </div>
    <div id="profilePage">
        <div class="profile-layout">
            {{-- BÊN TRÁI: ĐIỀU HƯỚNG --}}
            <aside class="profile-nav">
                <div class="profile-nav-title">
                    <i class="bi bi-person-circle"></i>
                    <span>Hồ sơ của tôi</span>
                </div>
                <ul class="profile-nav-list">
                    <li class="profile-nav-item">
                        <a
                            href="{{ route('user.profile.index', ['tab' => 'info']) }}"
                            class="profile-nav-link {{ $activeTab === 'info' ? 'active' : '' }}"
                            data-target="info">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Thông tin cá nhân</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a
                            href="{{ route('user.profile.index', ['tab' => 'orders']) }}"
                            class="profile-nav-link {{ $activeTab === 'orders' ? 'active' : '' }}"
                            data-target="orders">
                            <i class="bi bi-receipt"></i>
                            <span>Lịch sử đơn hàng</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a
                            href="{{ route('user.profile.index', ['tab' => 'addresses']) }}"
                            class="profile-nav-link {{ $activeTab === 'addresses' ? 'active' : '' }}"
                            data-target="addresses">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Sổ địa chỉ</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a
                            href="{{ route('user.profile.index', ['tab' => 'password']) }}"
                            class="profile-nav-link {{ $activeTab === 'password' ? 'active' : '' }}"
                            data-target="password">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Đổi mật khẩu</span>
                        </a>
                    </li>
                </ul>
            </aside>

            {{-- BÊN PHẢI: NỘI DUNG --}}
            <main class="profile-main">
                {{-- Thông tin cá nhân --}}
                <section class="profile-section {{ $activeTab === 'info' ? 'active' : '' }}" data-section="info">
                    <div class="profile-info-card">
                        <div class="profile-info-card-header">
                            <div>
                                <h2 class="profile-section-title mb-1">Thông tin cá nhân</h2>
                                <p class="profile-section-subtitle mb-0">
                                    Một số thông tin cơ bản về tài khoản của bạn.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="edit-info-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editProfileModal">
                                <i class="bi bi-pencil-square me-1"></i>
                                Chỉnh sửa
                            </button>
                        </div>

                        <div class="profile-info-grid">
                            <div>
                                <div class="profile-info-item-label">Họ và tên</div>
                                <div class="profile-info-item-value">
                                    {{ $user->name }}
                                </div>
                            </div>
                            <div>
                                <div class="profile-info-item-label">Số điện thoại</div>
                                <div class="profile-info-item-value">
                                    {{ $user->phone ?? 'Chưa cập nhật' }}
                                </div>
                            </div>
                            <div>
                                <div class="profile-info-item-label">Email</div>
                                <div class="profile-info-item-value">
                                    {{ $user->email }}
                                </div>
                            </div>
                            <div>
                                <div class="profile-info-item-label">Ngày sinh</div>
                                <div class="profile-info-item-value">
                                    {{ $user->birthday ?? 'Chưa cập nhật' }}
                                </div>
                            </div>
                            <div>
                                <div class="profile-info-item-label">Giới tính</div>
                                <div class="profile-info-item-value">
                                    @php
                                    $gender = $user->gender ?? null;
                                    @endphp
                                    @if ($gender === 'male')
                                    Nam
                                    @elseif ($gender === 'female')
                                    Nữ
                                    @elseif ($gender === 'other')
                                    Khác
                                    @else
                                    Chưa cập nhật
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="profile-info-item-label">Tham gia từ</div>
                                <div class="profile-info-item-value">
                                    {{ optional($user->created_at)->format('m/Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Lịch sử đơn hàng --}}
                <section class="profile-section {{ $activeTab === 'orders' ? 'active' : '' }}" data-section="orders">
                    <div class="orders-section-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h2 class="profile-section-title mb-1">Lịch sử đơn hàng</h2>
                                <p class="profile-section-subtitle mb-0">
                                    Danh sách các đơn hàng của bạn.
                                </p>
                            </div>

                            <form method="GET" class="d-flex align-items-center">
                                <label class="me-2 mb-0">Hiển thị</label>
                                <select
                                    class="form-select form-select-sm w-auto"
                                    name="per_page_order"
                                    onchange="this.form.submit()">
                                    <option value="10" {{ $pp === 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ $pp === 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ $pp === 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <input type="hidden" name="tab" value="orders">
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle order-table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($orders->count() > 0)
                                    @foreach ($orders as $order)
                                    @php
                                    $statusRaw = strtoupper($order->status ?? '');
                                    $statusLabel = 'Không xác định';
                                    $badgeClass = 'badge-status-pending';

                                    if (in_array($statusRaw, ['PENDING', 'CONFIRMED', 'PICKING', 'SHIPPED', 'PROCESSING', 'SHIPPING'], true)) {
                                    $statusLabel = 'Đang xử lý';
                                    $badgeClass = 'badge-status-pending';
                                    } elseif (in_array($statusRaw, ['DELIVERED', 'COMPLETED'], true)) {
                                    $statusLabel = 'Hoàn thành';
                                    $badgeClass = 'badge-status-success';
                                    } elseif (in_array($statusRaw, ['CANCELLED', 'RETURNED'], true)) {
                                    $statusLabel = 'Đã hủy';
                                    $badgeClass = 'badge-status-cancel';
                                    }
                                    @endphp
                                    <tr>
                                        <td>{{ $order->code }}</td>
                                        <td>
                                            {{ optional($order->placed_at)->timezone(config('app.timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i') }}
                                        </td>
                                        <td>{{ number_format($order->grand_total_vnd, 0, ',', '.') }}đ</td>
                                        <td>
                                            <span class="badge-status {{ $badgeClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('user.profile.orders.show', $order->id) }}">
                                                <i class="fa fa-eye icon-eye-view-order-detail"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Bạn chưa có đơn hàng nào.
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($orders->hasPages())
                    <div
                        class="mt-3 d-flex justify-content-center orders-pagination"
                        data-profile-orders-pagination>
                        {{ $orders->appends(array_merge(request()->except('page'), ['tab' => 'orders']))->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </section>


                {{-- Sổ địa chỉ --}}
                <section class="profile-section {{ $activeTab === 'addresses' ? 'active' : '' }}" data-section="addresses">
                    <div class="address-header">
                        <div class="address-header-text">
                            <div class="address-header-title">Sổ địa chỉ</div>
                            <div class="address-header-subtitle">Quản lý các địa chỉ thường dùng</div>
                        </div>
                        <button
                            type="button"
                            class="address-add-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#addAddressModal">
                            <span>+</span>
                            <span>Thêm địa chỉ</span>
                        </button>
                    </div>

                    @if ($addresses->count() > 0)
                    @foreach ($addresses as $address)
                    <div class="address-card">
                        <div class="address-card-header">
                            <div class="address-card-title">
                                <i class="bi bi-geo-alt"></i>
                                <span>{{ $address->address }}</span>
                                @if ($address->default)
                                <span class="badge bg-primary ms-2">Mặc định</span>
                                @endif
                            </div>
                            <div class="address-card-actions">
                                {{-- Sửa địa chỉ --}}
                                <button
                                    type="button"
                                    class="address-edit-btn"
                                    aria-label="Sửa địa chỉ"
                                    data-id="{{ $address->id }}"
                                    data-update-url="{{ route('user.profile.updateAddress', $address->id) }}"
                                    data-address="{{ $address->address }}"
                                    data-province-id="{{ $address->address_province_id }}"
                                    data-ward-id="{{ $address->address_ward_id }}"
                                    data-note="{{ $address->note ?? '' }}"
                                    data-default="{{ $address->default ? '1' : '0' }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                {{-- Xoá địa chỉ --}}
                                <form
                                    method="POST"
                                    action="{{ route('user.profile.destroyAddress', $address->id) }}"
                                    class="d-inline js-address-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        aria-label="Xóa địa chỉ"
                                        class="btn btn-link text-danger p-0 remove-btn js-address-delete-btn"
                                        data-confirm-message="Bạn có chắc chắn muốn xoá địa chỉ này không?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                        <div class="address-card-body">
                            {{ $address->address }}
                            @if ($address->ward || $address->province)
                            , {{ optional($address->ward)->name }},
                            {{ optional($address->province)->name }}
                            @endif
                            @if (!empty($address->note))
                            <br>
                            <small class="text-muted">Ghi chú: {{ $address->note }}</small>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted mb-0">
                        Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới để đặt hàng nhanh hơn.
                    </p>
                    @endif
                </section>

                {{-- Đổi mật khẩu --}}
                <section class="profile-section {{ $activeTab === 'password' ? 'active' : '' }}" data-section="password">
                    <h2 class="profile-section-title">Đổi mật khẩu</h2>
                    <p class="profile-section-subtitle">Cập nhật mật khẩu để tăng tính bảo mật cho tài khoản.</p>

                    <form class="password-form">
                        <div class="mb-3">
                            <label class="form-label" for="currentPassword">Mật khẩu hiện tại</label>
                            <input type="password" id="currentPassword" class="form-control" placeholder="Nhập mật khẩu hiện tại">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="newPassword">Mật khẩu mới</label>
                            <input type="password" id="newPassword" class="form-control" placeholder="Nhập mật khẩu mới">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="confirmPassword">Xác nhận mật khẩu mới</label>
                            <input type="password" id="confirmPassword" class="form-control" placeholder="Nhập lại mật khẩu mới">
                        </div>
                        <button type="button" class="btn btn-primary mt-2">Lưu thay đổi</button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</div>
@include('partials.ui.profileOverview.edit-info-modal')
@include('partials.ui.profileOverview.add-address-modal')
@include('partials.ui.profileOverview.update-address-modal')
@include('partials.ui.confirm-modal')
@endsection

@push('scripts')
@vite(['resources/js/pages/profileOverview.js'])

@if ($errors->profile->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('editProfileModal');
        if (!modalEl || !window.bootstrap) {
            return;
        }
        var modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
</script>
@endif

@if ($errors->addressStore->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('addAddressModal');
        if (!el || !window.bootstrap) {
            return;
        }
        var modal = window.bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    });
</script>
@endif

@if ($errors->addressUpdate->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('updateAddressModal');
        if (!el || !window.bootstrap) {
            return;
        }
        var modal = window.bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    });
</script>
@endif
@endpush