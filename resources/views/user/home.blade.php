@extends('layouts.user')
@section('title','Trang chủ')

@section('content')
    <div id="homePage" class="page-content">
        <!-- Hero Section -->
        <section class="hero-section" id="home">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <h1 class="display-4 fw-bold mb-4">Khám phá thế giới tri thức</h1>
                            <p class="lead mb-4">Hàng nghìn cuốn sách hay đang chờ bạn khám phá. Từ tiểu thuyết đến sách
                                chuyên môn, tất cả đều có tại BookStore.</p>
                            <div class="d-flex flex-wrap gap-3">
                                <button class="btn btn-primary btn-lg" onclick="scrollToSection('categories')">
                                    <i class="fas fa-compass me-2"></i>Khám phá ngay
                                </button>
                                <button class="btn btn-outline-light btn-lg" onclick="scrollToSection('bestsellers')">
                                    <i class="fas fa-fire me-2"></i>Sách hot
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-center">
                            <div class="position-relative">
                                <i class="fas fa-book-open" style="font-size: 15rem; opacity: 0.3;"></i>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <div class="d-flex justify-content-center">
                                        <div
                                            class="bg-white rounded-circle p-3 shadow me-3 animate__animated animate__fadeInUp">
                                            <i class="fas fa-star text-warning" style="font-size: 2rem;"></i>
                                        </div>
                                        <div
                                            class="bg-white rounded-circle p-3 shadow animate__animated animate__fadeInUp animate__delay-1s">
                                            <i class="fas fa-heart text-danger" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-item">
                            <div class="stat-number" data-target="15000">0</div>
                            <p class="mb-0">Đầu sách</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-item">
                            <div class="stat-number" data-target="50000">0</div>
                            <p class="mb-0">Khách hàng</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-item">
                            <div class="stat-number" data-target="98">0</div>
                            <p class="mb-0">% Hài lòng</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="stat-item">
                            <div class="stat-number" data-target="24">0</div>
                            <p class="mb-0">Giờ hỗ trợ</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-5 bg-light" id="categories">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Danh mục sách</h2>
                    <p class="lead text-muted">Tìm kiếm theo sở thích của bạn</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="category-card" onclick="filterByCategory('novel')">
                            <div class="category-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h5 class="fw-bold">Tiểu thuyết</h5>
                            <p class="text-muted mb-0">Những câu chuyện cảm động</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="category-card" onclick="filterByCategory('business')">
                            <div class="category-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h5 class="fw-bold">Kinh doanh</h5>
                            <p class="text-muted mb-0">Phát triển sự nghiệp</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="category-card" onclick="filterByCategory('psychology')">
                            <div class="category-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h5 class="fw-bold">Tâm lý học</h5>
                            <p class="text-muted mb-0">Hiểu về con người</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="category-card" onclick="filterByCategory('children')">
                            <div class="category-icon">
                                <i class="fas fa-child"></i>
                            </div>
                            <h5 class="fw-bold">Thiếu nhi</h5>
                            <p class="text-muted mb-0">Nuôi dưỡng tâm hồn trẻ</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bestsellers Section -->
        <section class="py-5" id="bestsellers">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h2 class="display-5 fw-bold text-primary">Sách bán chạy</h2>
                        <p class="lead text-muted">Những cuốn sách được yêu thích nhất</p>
                    </div>
                    <button class="btn btn-outline-primary" onclick="loadMoreBooks()">
                        Xem thêm <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>

                <div class="row g-4" id="booksContainer">
                    <!-- Books will be loaded here -->
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary">Khách hàng nói gì</h2>
                    <p class="lead text-muted">Những phản hồi chân thực từ độc giả</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="testimonial-card">
                            <div class="rating mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="mb-4">"BookStore có bộ sưu tập sách rất phong phú. Giao hàng nhanh, đóng gói cẩn
                                thận. Tôi đã tìm được nhiều cuốn sách hay ở đây!"</p>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Nguyễn Văn An</h6>
                                    <small class="text-muted">Khách hàng thân thiết</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="testimonial-card">
                            <div class="rating mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="mb-4">"Website dễ sử dụng, tìm kiếm sách rất tiện lợi. Giá cả hợp lý, chất lượng
                                sách tốt. Sẽ tiếp tục ủng hộ BookStore!"</p>
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Trần Thị Bình</h6>
                                    <small class="text-muted">Độc giả đam mê</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="testimonial-card">
                            <div class="rating mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="mb-4">"Dịch vụ khách hàng tuyệt vời! Nhân viên tư vấn nhiệt tình, giúp tôi chọn
                                được những cuốn sách phù hợp với nhu cầu."</p>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Lê Minh Cường</h6>
                                    <small class="text-muted">Khách hàng mới</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center">
                        <h2 class="display-5 fw-bold mb-4">Đăng ký nhận tin</h2>
                        <p class="lead mb-4">Nhận thông báo về sách mới, ưu đãi đặc biệt và các sự kiện thú vị</p>

                        <form class="row g-3 justify-content-center" onsubmit="subscribeNewsletter(event)">
                            <div class="col-md-6">
                                <input type="email" class="form-control form-control-lg"
                                    placeholder="Nhập email của bạn" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <span class="button-text">Đăng ký</span>
                                    <div class="loading-spinner"></div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Product Details Page -->
    <div id="productPage" class="page-content" style="display: none;">
        <div class="container py-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" onclick="goHome()">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Chi tiết sản phẩm</li>
                </ol>
            </nav>

            <div class="row g-5">
                <div class="col-lg-6">
                    <div id="productCover" class="book-cover" style="height: 500px; border-radius: 15px;">
                        <div class="text-center">
                            <h3 id="productTitle">Tên sách</h3>
                            <p id="productAuthor">Tác giả</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h1 id="productTitleDetail" class="display-5 fw-bold mb-3">Tên sách</h1>
                    <p class="lead text-muted mb-4">Tác giả: <span id="productAuthorDetail">Tác giả</span></p>

                    <div class="mb-4">
                        <span class="h2 text-danger me-3" id="productPrice">299.000đ</span>
                        <span class="h5 text-muted text-decoration-line-through"
                            id="productOriginalPrice">399.000đ</span>
                    </div>

                    <div class="mb-4">
                        <div class="rating mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <span class="ms-2">(4.8/5 - 124 đánh giá)</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Mô tả sản phẩm</h5>
                        <p>Cuốn sách này mang đến những kiến thức quý báu và góc nhìn mới mẻ về cuộc sống. Với ngôn ngữ
                            dễ hiểu và nội dung phong phú, đây là lựa chọn tuyệt vời cho mọi độc giả.</p>

                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Nội dung chất lượng cao</li>
                            <li><i class="fas fa-check text-success me-2"></i>Dịch thuật chuẩn xác</li>
                            <li><i class="fas fa-check text-success me-2"></i>Bìa đẹp, in ấn sắc nét</li>
                            <li><i class="fas fa-check text-success me-2"></i>Giao hàng nhanh chóng</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Số lượng:</label>
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary" onclick="changeQuantity(-1)">-</button>
                            <input type="number" class="form-control text-center" value="1" min="1" id="quantity">
                            <button class="btn btn-outline-secondary" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <button class="btn btn-primary btn-lg flex-fill" onclick="addToCartFromProduct()">
                            <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
                        </button>
                        <button class="btn btn-outline-danger btn-lg" onclick="toggleWishlistFromProduct()">
                            <i class="fas fa-heart me-2"></i>Yêu thích
                        </button>
                    </div>

                    <input type="hidden" id="productId" value="">
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Page -->
    <div id="cartPage" class="page-content" style="display: none;">
        <div class="container py-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" onclick="goHome()">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Giỏ hàng</li>
                </ol>
            </nav>

            <h1 class="display-5 fw-bold mb-4">Giỏ hàng của bạn</h1>

            <div class="row">
                <div class="col-lg-8">
                    <div id="cartItems">
                        <!-- Cart items will be loaded here -->
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Tóm tắt đơn hàng</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span id="subtotal">0đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <span>30.000đ</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Tổng cộng:</strong>
                                <strong id="total">30.000đ</strong>
                            </div>
                            <button class="btn btn-primary w-100 btn-lg" onclick="checkout()">
                                <i class="fas fa-credit-card me-2"></i>Thanh toán
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Page -->
    <div id="profilePage" class="page-content" style="display: none;">
        <div class="container py-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" onclick="goHome()">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Tài khoản</li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-user text-white" style="font-size: 2rem;"></i>
                            </div>
                            <h5>Nguyễn Văn A</h5>
                            <p class="text-muted">Khách hàng thân thiết</p>
                        </div>
                    </div>

                    <div class="list-group mt-3">
                        <a href="#" class="list-group-item list-group-item-action active"
                            onclick="showProfileTab('info')">
                            <i class="fas fa-user me-2"></i>Thông tin cá nhân
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showProfileTab('orders')">
                            <i class="fas fa-shopping-bag me-2"></i>Đơn hàng của tôi
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showProfileTab('wishlist')">
                            <i class="fas fa-heart me-2"></i>Sách yêu thích
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showProfileTab('settings')">
                            <i class="fas fa-cog me-2"></i>Cài đặt
                        </a>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div id="profileInfo" class="profile-tab">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Thông tin cá nhân</h5>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Họ và tên</label>
                                            <input type="text" class="form-control" value="Nguyễn Văn A">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" value="nguyenvana@email.com">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" value="0123456789">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ngày sinh</label>
                                            <input type="date" class="form-control" value="1990-01-01">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Địa chỉ</label>
                                        <textarea class="form-control" rows="3">123 Đường ABC, Quận 1, TP.HCM</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="profileOrders" class="profile-tab" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Đơn hàng của tôi</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Mã đơn</th>
                                                <th>Ngày đặt</th>
                                                <th>Tổng tiền</th>
                                                <th>Trạng thái</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>#DH001</td>
                                                <td>15/12/2024</td>
                                                <td>599.000đ</td>
                                                <td><span class="badge bg-success">Đã giao</span></td>
                                                <td><button class="btn btn-sm btn-outline-primary">Xem chi tiết</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>#DH002</td>
                                                <td>10/12/2024</td>
                                                <td>299.000đ</td>
                                                <td><span class="badge bg-warning">Đang giao</span></td>
                                                <td><button class="btn btn-sm btn-outline-primary">Xem chi tiết</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="profileWishlist" class="profile-tab" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Sách yêu thích</h5>
                            </div>
                            <div class="card-body">
                                <div id="wishlistItems" class="row g-3">
                                    <!-- Wishlist items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="profileSettings" class="profile-tab" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Cài đặt tài khoản</h5>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Đổi mật khẩu</label>
                                        <input type="password" class="form-control mb-2"
                                            placeholder="Mật khẩu hiện tại">
                                        <input type="password" class="form-control mb-2" placeholder="Mật khẩu mới">
                                        <input type="password" class="form-control" placeholder="Xác nhận mật khẩu mới">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="emailNotifications"
                                                checked>
                                            <label class="form-check-label" for="emailNotifications">
                                                Nhận thông báo qua email
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="smsNotifications">
                                            <label class="form-check-label" for="smsNotifications">
                                                Nhận thông báo qua SMS
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- About Page -->
    <div id="aboutPage" class="page-content" style="display: none;">
        <div class="container py-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" onclick="goHome()">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Giới thiệu</li>
                </ol>
            </nav>

            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary">Về BookStore</h1>
                <p class="lead">Nơi kết nối bạn với tri thức</p>
            </div>

            <div class="row align-items-center mb-5">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">Câu chuyện của chúng tôi</h2>
                    <p class="mb-4">BookStore được thành lập với sứ mệnh mang tri thức đến gần hơn với mọi người. Chúng
                        tôi tin rằng sách là cầu nối giữa con người với kiến thức, giữa hiện tại và tương lai.</p>
                    <p class="mb-4">Với hơn 15.000 đầu sách từ các nhà xuất bản uy tín trong và ngoài nước, BookStore cam
                        kết cung cấp những cuốn sách chất lượng cao với giá cả hợp lý nhất.</p>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-book-open text-primary" style="font-size: 10rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-shipping-fast text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Giao hàng nhanh</h5>
                        <p class="text-muted">Giao hàng trong 24h tại TP.HCM và 2-3 ngày toàn quốc</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Chất lượng đảm bảo</h5>
                        <p class="text-muted">100% sách chính hãng, đổi trả trong 7 ngày</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-headset text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Hỗ trợ 24/7</h5>
                        <p class="text-muted">Đội ngũ tư vấn nhiệt tình, sẵn sàng hỗ trợ mọi lúc</p>
                    </div>
                </div>
            </div>

            <div class="bg-light rounded p-5 text-center">
                <h3 class="fw-bold mb-4">Tầm nhìn và sứ mệnh</h3>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary fw-bold">Tầm nhìn</h5>
                        <p>Trở thành nền tảng sách trực tuyến hàng đầu Việt Nam, nơi mọi người có thể dễ dàng tiếp cận
                            tri thức.</p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-primary fw-bold">Sứ mệnh</h5>
                        <p>Kết nối độc giả với những cuốn sách hay nhất, góp phần xây dựng một xã hội học tập suốt đời.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
