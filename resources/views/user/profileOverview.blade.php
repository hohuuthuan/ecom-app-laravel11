@extends('layouts.user')
@section('title','Quản lý hồ sơ')

@section('content')
@php
$activeTab = request('tab', 'info');
$validTabs = ['info', 'orders', 'addresses', 'password'];
if (!in_array($activeTab, $validTabs, true)) {
$activeTab = 'info';
}
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
            <!-- BÊN TRÁI: ĐIỀU HƯỚNG -->
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
                            data-target="info"
                            data-no-loading="1">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Thông tin cá nhân</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a
                            href="{{ route('user.profile.index', ['tab' => 'orders']) }}"
                            class="profile-nav-link {{ $activeTab === 'orders' ? 'active' : '' }}"
                            data-target="orders"
                            data-no-loading="1">
                            <i class="bi bi-receipt"></i>
                            <span>Lịch sử đơn hàng</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a
                            href="{{ route('user.profile.index', ['tab' => 'addresses']) }}"
                            class="profile-nav-link {{ $activeTab === 'addresses' ? 'active' : '' }}"
                            data-target="addresses"
                            data-no-loading="1">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Sổ địa chỉ</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a
                            href="{{ route('user.profile.index', ['tab' => 'password']) }}"
                            class="profile-nav-link {{ $activeTab === 'password' ? 'active' : '' }}"
                            data-target="password"
                            data-no-loading="1">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Đổi mật khẩu</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- BÊN PHẢI: NỘI DUNG TĨNH -->
            <main class="profile-main">
                <!-- Thông tin cá nhân -->
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

                <!-- Lịch sử đơn hàng -->
                <section class="profile-section {{ $activeTab === 'orders' ? 'active' : '' }}" data-section="orders">
                    <h2 class="profile-section-title">Lịch sử đơn hàng</h2>
                    <p class="profile-section-subtitle">Danh sách một số đơn hàng gần đây.</p>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle order-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $order)
                                @php
                                $status = $order->status;
                                $statusLabel = 'Không xác định';
                                $badgeClass = 'badge-status-pending';

                                if (in_array($status, ['pending', 'confirmed', 'picking', 'shipped'], true)) {
                                $statusLabel = 'Đang xử lý';
                                $badgeClass = 'badge-status-pending';
                                } elseif ($status === 'delivered') {
                                $statusLabel = 'Hoàn thành';
                                $badgeClass = 'badge-status-success';
                                } elseif (in_array($status, ['cancelled', 'returned'], true)) {
                                $statusLabel = 'Đã hủy';
                                $badgeClass = 'badge-status-cancel';
                                }
                                @endphp
                                <tr>
                                    <td>{{ $order->code }}</td>
                                    <td>{{ optional($order->placed_at)->format('d/m/Y') }}</td>
                                    <td>{{ number_format($order->grand_total_vnd, 0, ',', '.') }}đ</td>
                                    <td>
                                        <span class="badge-status {{ $badgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Bạn chưa có đơn hàng nào.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </section>

                <!-- Sổ địa chỉ -->
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
                    <div class="address-wrapper">
                        @forelse ($addresses as $address)
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
                                    {{-- Sau này gắn route edit/destroy vào đây --}}
                                    <button type="button" aria-label="Sửa địa chỉ">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" aria-label="Xóa địa chỉ">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
                                <small class="text-muted">{{ $address->note }}</small>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-muted mb-0">
                            Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới để đặt hàng nhanh hơn.
                        </p>
                        @endforelse
                    </div>
                </section>

                <!-- Đổi mật khẩu -->
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
@endsection

@push('scripts')
@vite(['resources/js/pages/profileOverview.js'])

@if ($errors->profile->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('editProfileModal');
        if (!modalEl) return;
        var Modal = window.bootstrap && window.bootstrap.Modal ? window.bootstrap.Modal : null;
        if (!Modal) return;
        var modal = Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
</script>
@endif
@endpush