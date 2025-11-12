@extends('layouts.user')
@section('title','Quản lý hồ sơ')

@section('content')
<div class="container profile-overview-page">
    <div class="page-header">
        <h1 class="pageTitle">HỒ SƠ CỦA BẠN </h1>
        <div class="page-nav">
            <a class="btn continue-shopping" href="{{ route('home') }}">
                <i class="bi bi-arrow-left"></i>
                Quay lại trang chủ
            </a>
        </div>
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
                        <a href="#" class="profile-nav-link active" data-target="info">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Thông tin cá nhân</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a href="#" class="profile-nav-link" data-target="orders">
                            <i class="bi bi-receipt"></i>
                            <span>Lịch sử đơn hàng</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a href="#" class="profile-nav-link" data-target="addresses">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Sổ địa chỉ</span>
                        </a>
                    </li>
                    <li class="profile-nav-item">
                        <a href="#" class="profile-nav-link" data-target="password">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Đổi mật khẩu</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- BÊN PHẢI: NỘI DUNG TĨNH -->
            <main class="profile-main">
                <!-- Thông tin cá nhân -->
                <section class="profile-section active" data-section="info">
                    <h2 class="profile-section-title">Thông tin cá nhân</h2>
                    <p class="profile-section-subtitle">Một số thông tin cơ bản về tài khoản của bạn.</p>

                    <div class="profile-info-grid">
                        <div>
                            <div class="profile-info-item-label">Họ và tên</div>
                            <div class="profile-info-item-value">Nguyễn Văn A</div>
                        </div>
                        <div>
                            <div class="profile-info-item-label">Số điện thoại</div>
                            <div class="profile-info-item-value">0901 234 567</div>
                        </div>
                        <div>
                            <div class="profile-info-item-label">Email</div>
                            <div class="profile-info-item-value">nguyenvana@example.com</div>
                        </div>
                        <div>
                            <div class="profile-info-item-label">Ngày sinh</div>
                            <div class="profile-info-item-value">01/01/1990</div>
                        </div>
                        <div>
                            <div class="profile-info-item-label">Giới tính</div>
                            <div class="profile-info-item-value">Nam</div>
                        </div>
                        <div>
                            <div class="profile-info-item-label">Tham gia từ</div>
                            <div class="profile-info-item-value">05/2024</div>
                        </div>
                    </div>
                </section>

                <!-- Lịch sử đơn hàng -->
                <section class="profile-section" data-section="orders">
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
                                <tr>
                                    <td>#HD123456</td>
                                    <td>10/11/2025</td>
                                    <td>350.000đ</td>
                                    <td><span class="badge-status badge-status-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123455</td>
                                    <td>05/11/2025</td>
                                    <td>120.000đ</td>
                                    <td><span class="badge-status badge-status-pending">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123454</td>
                                    <td>30/10/2025</td>
                                    <td>89.000đ</td>
                                    <td><span class="badge-status badge-status-cancel">Đã hủy</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123456</td>
                                    <td>10/11/2025</td>
                                    <td>350.000đ</td>
                                    <td><span class="badge-status badge-status-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123455</td>
                                    <td>05/11/2025</td>
                                    <td>120.000đ</td>
                                    <td><span class="badge-status badge-status-pending">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123454</td>
                                    <td>30/10/2025</td>
                                    <td>89.000đ</td>
                                    <td><span class="badge-status badge-status-cancel">Đã hủy</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123456</td>
                                    <td>10/11/2025</td>
                                    <td>350.000đ</td>
                                    <td><span class="badge-status badge-status-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123455</td>
                                    <td>05/11/2025</td>
                                    <td>120.000đ</td>
                                    <td><span class="badge-status badge-status-pending">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123454</td>
                                    <td>30/10/2025</td>
                                    <td>89.000đ</td>
                                    <td><span class="badge-status badge-status-cancel">Đã hủy</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123456</td>
                                    <td>10/11/2025</td>
                                    <td>350.000đ</td>
                                    <td><span class="badge-status badge-status-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123455</td>
                                    <td>05/11/2025</td>
                                    <td>120.000đ</td>
                                    <td><span class="badge-status badge-status-pending">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123454</td>
                                    <td>30/10/2025</td>
                                    <td>89.000đ</td>
                                    <td><span class="badge-status badge-status-cancel">Đã hủy</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123456</td>
                                    <td>10/11/2025</td>
                                    <td>350.000đ</td>
                                    <td><span class="badge-status badge-status-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123455</td>
                                    <td>05/11/2025</td>
                                    <td>120.000đ</td>
                                    <td><span class="badge-status badge-status-pending">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123454</td>
                                    <td>30/10/2025</td>
                                    <td>89.000đ</td>
                                    <td><span class="badge-status badge-status-cancel">Đã hủy</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123456</td>
                                    <td>10/11/2025</td>
                                    <td>350.000đ</td>
                                    <td><span class="badge-status badge-status-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123455</td>
                                    <td>05/11/2025</td>
                                    <td>120.000đ</td>
                                    <td><span class="badge-status badge-status-pending">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123454</td>
                                    <td>30/10/2025</td>
                                    <td>89.000đ</td>
                                    <td><span class="badge-status badge-status-cancel">Đã hủy</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123456</td>
                                    <td>10/11/2025</td>
                                    <td>350.000đ</td>
                                    <td><span class="badge-status badge-status-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123455</td>
                                    <td>05/11/2025</td>
                                    <td>120.000đ</td>
                                    <td><span class="badge-status badge-status-pending">Đang giao</span></td>
                                </tr>
                                <tr>
                                    <td>#HD123454</td>
                                    <td>30/10/2025</td>
                                    <td>89.000đ</td>
                                    <td><span class="badge-status badge-status-cancel">Đã hủy</span></td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Sổ địa chỉ -->
                <section class="profile-section" data-section="addresses">
                    <div class="address-header">
                        <div class="address-header-text">
                            <div class="address-header-title">Sổ địa chỉ</div>
                            <div class="address-header-subtitle">Quản lý các địa chỉ thường dùng</div>
                        </div>
                        <button type="button" class="address-add-btn">
                            <span>+</span>
                            <span>Thêm địa chỉ</span>
                        </button>
                    </div>
                    <div class="address-card">
                        <div class="address-card-header">
                            <div class="address-card-title">
                                <i class="bi bi-geo-alt"></i>
                                <span>Địa chỉ</span>
                            </div>
                            <div class="address-card-actions">
                                <button type="button" aria-label="Sửa địa chỉ">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" aria-label="Xóa địa chỉ">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="address-card-body">
                            Số 4, An Bình, Cần Thơ, Việt Nam
                        </div>
                    </div>
                </section>

                <!-- Đổi mật khẩu -->
                <section class="profile-section" data-section="password">
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

<script>
    (function() {
        var navLinks = document.querySelectorAll('.profile-nav-link');
        var sections = document.querySelectorAll('.profile-section');

        navLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var target = link.getAttribute('data-target');

                navLinks.forEach(function(item) {
                    item.classList.remove('active');
                });
                sections.forEach(function(section) {
                    section.classList.remove('active');
                });

                link.classList.add('active');
                var section = document.querySelector('.profile-section[data-section="' + target + '"]');
                if (section) {
                    section.classList.add('active');
                }
            });
        });
    })();
</script>
@endsection

@push('scripts')
@vite(['resources/js/pages/cart-page.js'])
@endpush