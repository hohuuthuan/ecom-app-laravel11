@extends('layouts.user')
@section('title','Quản lý hồ sơ')

@section('content')
@php
$activeTab = request('tab', 'stats');
$validTabs = ['stats', 'info', 'orders', 'addresses', 'password'];
if (!in_array($activeTab, $validTabs, true)) {
$activeTab = 'stats';
}

$pp = (int) request('per_page_order', 10);

$headerTitles = [
'stats' => 'THỐNG KÊ ĐƠN HÀNG',
'info' => 'HỒ SƠ CỦA BẠN',
'orders' => 'LỊCH SỬ ĐƠN HÀNG',
'addresses' => 'SỔ ĐỊA CHỈ CỦA BẠN',
'password' => 'ĐỔI MẬT KHẨU',
];
$headerTitle = $headerTitles[$activeTab] ?? 'HỒ SƠ CỦA BẠN';
@endphp



<div class="container profile-overview-page">
    <div class="page-header">
        <div class="page-nav">
            <a class="btn continue-shopping" href="{{ route('home') }}">
                <i class="bi bi-arrow-left"></i>
                Quay lại trang chủ
            </a>
        </div>
        <h1 class="pageTitle">{{ $headerTitle }}</h1>
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
                            href="{{ route('user.profile.index', ['tab' => 'stats']) }}"
                            class="profile-nav-link {{ $activeTab === 'stats' ? 'active' : '' }}"
                            data-target="stats">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Thống kê</span>
                        </a>
                    </li>
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
                <section class="profile-section {{ $activeTab === 'stats' ? 'active' : '' }}" data-section="stats">
                    @include('user.profile.partials.tab-stats', [
                    'stats' => $stats ?? [],
                    ])
                </section>

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
                            {{-- Cột 1: Họ và tên, Email, Giới tính --}}
                            <div class="profile-info-column">
                                <div>
                                    <div class="profile-info-item-label">Họ và tên</div>
                                    <div class="profile-info-item-value">
                                        {{ $user->name }}
                                    </div>
                                </div>
                                <div>
                                    <div class="profile-info-item-label">Email</div>
                                    <div class="profile-info-item-value">
                                        {{ $user->email }}
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
                            </div>

                            {{-- Cột 2: Số điện thoại, Ngày sinh, Tham gia từ --}}
                            <div class="profile-info-column">
                                <div>
                                    <div class="profile-info-item-label">Số điện thoại</div>
                                    <div class="profile-info-item-value">
                                        {{ $user->phone ?? 'Chưa cập nhật' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="profile-info-item-label">Ngày sinh</div>
                                    <div class="profile-info-item-value">
                                        {{ $user->birthday ?? 'Chưa cập nhật' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="profile-info-item-label">Tham gia từ</div>
                                    <div class="profile-info-item-value">
                                        {{ optional($user->created_at)->format('m/Y') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Cột 3: Ảnh đại diện --}}
                            <div class="profile-info-column profile-avatar-column">
                                <div class="profile-avatar-container">
                                    <div class="profile-info-item-label">Ảnh đại diện</div>
                                    <img 
                                        src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('storage/avatars/base-avatar.jpg') }}" 
                                        alt="Avatar" 
                                        class="rounded-circle" 
                                        width="160" 
                                        height="160" 
                                        style="object-fit: cover; border: 2px solid #e0e0e0;">
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

                            <form method="GET" class="d-flex align-items-center js-orders-per-page-form">
                                <label class="me-2 mb-0">Hiển thị</label>
                                <select
                                    class="form-select form-select-sm w-auto"
                                    name="per_page_order">
                                    <option value="10" {{ $pp === 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ $pp === 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ $pp === 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <input type="hidden" name="tab" value="orders">
                            </form>
                        </div>

                        <div class="orders-filter-bar">
                            <div class="orders-filter">
                                <button
                                    type="button"
                                    class="orders-filter-btn {{ ($statusGroup ?? '') === '' ? 'active' : '' }}"
                                    data-status-group="">
                                    Tất cả
                                </button>

                                {{-- Nhóm các trạng thái: PENDING, PROCESSING, PICKING, SHIPPING --}}
                                <button
                                    type="button"
                                    class="orders-filter-btn {{ ($statusGroup ?? '') === 'processing' ? 'active' : '' }}"
                                    data-status-group="processing">
                                    Đang xử lý (chờ / tiếp nhận / chuẩn bị / giao vận)
                                </button>

                                {{-- COMPLETED --}}
                                <button
                                    type="button"
                                    class="orders-filter-btn {{ ($statusGroup ?? '') === 'completed' ? 'active' : '' }}"
                                    data-status-group="completed">
                                    Hoàn tất đơn hàng
                                </button>

                                {{-- CANCELLED, RETURNED, DELIVERY_FAILED --}}
                                <button
                                    type="button"
                                    class="orders-filter-btn {{ ($statusGroup ?? '') === 'cancelled' ? 'active' : '' }}"
                                    data-status-group="cancelled">
                                    Đã hủy / Hoàn / Giao thất bại
                                </button>
                            </div>

                            <div class="orders-date-filter">
                                <div class="orders-date-filter-item">
                                    <label class="orders-date-filter-label">Từ ngày</label>
                                    <input
                                        type="date"
                                        class="form-control form-control-sm js-orders-date-from"
                                        value="{{ $createdFrom ?? request('created_from') }}">
                                </div>
                                <div class="orders-date-filter-item">
                                    <label class="orders-date-filter-label">Đến ngày</label>
                                    <input
                                        type="date"
                                        class="form-control form-control-sm js-orders-date-to"
                                        value="{{ $createdTo ?? request('created_to') }}">
                                </div>
                                <div class="orders-date-filter-actions">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary js-orders-apply-date">
                                        Lọc
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link text-muted js-orders-clear-date">
                                        Xoá lọc
                                    </button>
                                </div>
                            </div>
                        </div>


                        <div
                            id="ordersAjaxWrapper"
                            data-orders-container
                            data-orders-url="{{ route('user.profile.index') }}"
                            data-orders-tab="orders">
                            @include('user.profile.partials.ordersTable', [
                            'orders' => $orders,
                            'orderReviewStats' => $orderReviewStats ?? [],
                            ])
                        </div>
                    </div>
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