

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('library/select2-setup.js') }}"></script>
<script src="{{ asset('library/back-to-top.js') }}"></script>

    <script>
        // Sample book data
        const books = [
            {
                id: 1,
                title: "Sapiens: Lược sử loài người",
                author: "Yuval Noah Harari",
                price: "299.000đ",
                originalPrice: "399.000đ",
                cover: "linear-gradient(45deg, #667eea, #764ba2)",
                category: "psychology"
            },
            {
                id: 2,
                title: "Atomic Habits",
                author: "James Clear",
                price: "249.000đ",
                originalPrice: "329.000đ",
                cover: "linear-gradient(45deg, #f093fb, #f5576c)",
                category: "business"
            },
            {
                id: 3,
                title: "Thinking, Fast and Slow",
                author: "Daniel Kahneman",
                price: "329.000đ",
                originalPrice: "429.000đ",
                cover: "linear-gradient(45deg, #4facfe, #00f2fe)",
                category: "psychology"
            },
            {
                id: 4,
                title: "Nhà giả kim",
                author: "Paulo Coelho",
                price: "179.000đ",
                originalPrice: "229.000đ",
                cover: "linear-gradient(45deg, #43e97b, #38f9d7)",
                category: "novel"
            },
            {
                id: 5,
                title: "Rich Dad Poor Dad",
                author: "Robert Kiyosaki",
                price: "199.000đ",
                originalPrice: "259.000đ",
                cover: "linear-gradient(45deg, #fa709a, #fee140)",
                category: "business"
            },
            {
                id: 6,
                title: "Dế Mèn phiêu lưu ký",
                author: "Tô Hoài",
                price: "89.000đ",
                originalPrice: "119.000đ",
                cover: "linear-gradient(45deg, #a8edea, #fed6e3)",
                category: "children"
            }
        ];

        let cart = [];
        let wishlist = [];

        // Load books on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadBooks();
            animateStats();
        });

        // Load books into container
        function loadBooks() {
            const container = document.getElementById('booksContainer');
            container.innerHTML = '';

            books.forEach(book => {
                const bookCard = createBookCard(book);
                container.appendChild(bookCard);
            });
        }

        // Create book card element
        function createBookCard(book) {
            const col = document.createElement('div');
            col.className = 'col-lg-4 col-md-6';

            col.innerHTML = `
                <div class="card book-card">
                    <div class="book-cover" style="background: ${book.cover};">
                        <div>
                            <h5 class="mb-2">${book.title}</h5>
                            <p class="mb-0">${book.author}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">${book.title}</h6>
                        <p class="card-text text-muted">${book.author}</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="price">${book.price}</span>
                                <small class="text-muted text-decoration-line-through ms-2">${book.originalPrice}</small>
                            </div>
                            <button class="btn btn-outline-danger btn-sm" onclick="toggleWishlistItem(${book.id})">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="showProductDetails(${book.id})">
                                <i class="fas fa-eye me-2"></i>Xem chi tiết
                            </button>
                            <button class="btn btn-primary" onclick="addToCart(${book.id})">
                                <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                </div>
            `;

            return col;
        }

        // Add to cart function
        function addToCart(bookId) {
            const book = books.find(b => b.id === bookId);
            if (book) {
                const existingItem = cart.find(item => item.id === bookId);
                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push({ ...book, quantity: 1 });
                }
                updateCartBadge();
                showToast('Đã thêm vào giỏ hàng!', 'success');
            }
        }

        // Toggle wishlist item
        function toggleWishlistItem(bookId) {
            const index = wishlist.findIndex(id => id === bookId);
            if (index > -1) {
                wishlist.splice(index, 1);
                showToast('Đã xóa khỏi danh sách yêu thích', 'info');
            } else {
                wishlist.push(bookId);
                showToast('Đã thêm vào danh sách yêu thích!', 'success');
            }
            updateWishlistBadge();
        }

        // Update cart badge
        function updateCartBadge() {
            const badge = document.getElementById('cartBadge');
            const headerBadge = document.getElementById('headerCartBadge');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            badge.textContent = totalItems;
            headerBadge.textContent = totalItems;
        }

        // Update wishlist badge
        function updateWishlistBadge() {
            const badge = document.getElementById('wishlistCount');
            badge.textContent = wishlist.length;
        }

        // Show cart
        function showCart() {
            navigateToPage('cart');
        }

        // Show profile
        function showProfile() {
            navigateToPage('profile');
        }

        // Navigation system
        function navigateToPage(page) {
            // Hide all pages
            document.querySelectorAll('.page-content').forEach(p => p.style.display = 'none');

            // Show selected page
            const targetPage = document.getElementById(page + 'Page');
            if (targetPage) {
                targetPage.style.display = 'block';
                window.scrollTo(0, 0);
            }
        }

        // Show product details
        function showProductDetails(bookId) {
            const book = books.find(b => b.id === bookId);
            if (book) {
                // Update product details page
                document.getElementById('productTitle').textContent = book.title;
                document.getElementById('productAuthor').textContent = book.author;
                document.getElementById('productPrice').textContent = book.price;
                document.getElementById('productOriginalPrice').textContent = book.originalPrice;
                document.getElementById('productCover').style.background = book.cover;
                document.getElementById('productId').value = book.id;

                navigateToPage('product');
            }
        }

        // Go back to home
        function goHome() {
            document.querySelectorAll('.page-content').forEach(p => p.style.display = 'none');
            document.getElementById('homePage').style.display = 'block';
            window.scrollTo(0, 0);
        }

        // Toggle wishlist
        function toggleWishlist() {
            if (wishlist.length === 0) {
                showToast('Danh sách yêu thích trống!', 'warning');
                return;
            }

            const wishlistBooks = books.filter(book => wishlist.includes(book.id));
            let wishlistContent = 'Sách yêu thích:\n\n';
            wishlistBooks.forEach(book => {
                wishlistContent += `${book.title} - ${book.author}\n`;
            });
            alert(wishlistContent);
        }

        // Perform search
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            if (!searchTerm) {
                showToast('Vui lòng nhập từ khóa tìm kiếm!', 'warning');
                return;
            }

            const results = books.filter(book =>
                book.title.toLowerCase().includes(searchTerm) ||
                book.author.toLowerCase().includes(searchTerm)
            );

            if (results.length > 0) {
                showToast(`Tìm thấy ${results.length} kết quả cho "${searchTerm}"`, 'success');
                // In a real app, you would filter the displayed books
            } else {
                showToast(`Không tìm thấy kết quả cho "${searchTerm}"`, 'info');
            }
        }

        // Filter by category
        function filterByCategory(category) {
            const filteredBooks = books.filter(book => book.category === category);
            showToast(`Hiển thị ${filteredBooks.length} sách trong danh mục này`, 'info');
            // In a real app, you would update the displayed books
        }

        // Load more books
        function loadMoreBooks() {
            showToast('Đang tải thêm sách...', 'info');
            // In a real app, you would load more books from the server
        }

        // Scroll to section
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Subscribe to newsletter
        function subscribeNewsletter(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            const button = event.target.querySelector('button[type="submit"]');
            const buttonText = button.querySelector('.button-text');
            const spinner = button.querySelector('.loading-spinner');

            // Show loading
            buttonText.style.display = 'none';
            spinner.style.display = 'inline-block';
            button.disabled = true;

            // Simulate API call
            setTimeout(() => {
                buttonText.style.display = 'inline';
                spinner.style.display = 'none';
                button.disabled = false;

                showToast(`Cảm ơn! Chúng tôi đã ghi nhận email: ${email}`, 'success');
                event.target.reset();
            }, 2000);
        }

        // Animate statistics
        function animateStats() {
            const stats = document.querySelectorAll('.stat-number');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.getAttribute('data-target'));
                        animateNumber(entry.target, target);
                        observer.unobserve(entry.target);
                    }
                });
            });

            stats.forEach(stat => observer.observe(stat));
        }

        // Animate number counting
        function animateNumber(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 20);
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : type === 'error' ? 'danger' : 'info'} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'times-circle' : 'info-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;

            document.body.appendChild(toast);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 3000);
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Product page functions
        function changeQuantity(change) {
            const quantityInput = document.getElementById('quantity');
            let currentValue = parseInt(quantityInput.value);
            currentValue += change;
            if (currentValue < 1) currentValue = 1;
            quantityInput.value = currentValue;
        }

        function addToCartFromProduct() {
            const bookId = parseInt(document.getElementById('productId').value);
            const quantity = parseInt(document.getElementById('quantity').value);

            const book = books.find(b => b.id === bookId);
            if (book) {
                const existingItem = cart.find(item => item.id === bookId);
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    cart.push({ ...book, quantity: quantity });
                }
                updateCartBadge();
                showToast(`Đã thêm ${quantity} cuốn "${book.title}" vào giỏ hàng!`, 'success');
            }
        }

        function toggleWishlistFromProduct() {
            const bookId = parseInt(document.getElementById('productId').value);
            toggleWishlistItem(bookId);
        }

        // Cart page functions
        function loadCartItems() {
            const cartContainer = document.getElementById('cartItems');
            const subtotalElement = document.getElementById('subtotal');
            const totalElement = document.getElementById('total');

            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Giỏ hàng trống</h4>
                        <p class="text-muted">Hãy thêm một số sách vào giỏ hàng của bạn</p>
                        <button class="btn btn-primary" onclick="goHome()">Tiếp tục mua sắm</button>
                    </div>
                `;
                subtotalElement.textContent = '0đ';
                totalElement.textContent = '30.000đ';
                return;
            }

            let subtotal = 0;
            cartContainer.innerHTML = '';

            cart.forEach(item => {
                const price = parseInt(item.price.replace(/[^\d]/g, ''));
                const itemTotal = price * item.quantity;
                subtotal += itemTotal;

                const cartItem = document.createElement('div');
                cartItem.className = 'card mb-3';
                cartItem.innerHTML = `
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <div class="book-cover" style="height: 80px; width: 60px; ${item.cover}; border-radius: 8px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1">${item.title}</h6>
                                <small class="text-muted">${item.author}</small>
                            </div>
                            <div class="col-md-2">
                                <span class="fw-bold">${item.price}</span>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" onclick="updateCartQuantity(${item.id}, -1)">-</button>
                                    <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                                    <button class="btn btn-outline-secondary" onclick="updateCartQuantity(${item.id}, 1)">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <div class="fw-bold mb-2">${itemTotal.toLocaleString()}đ</div>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                cartContainer.appendChild(cartItem);
            });

            subtotalElement.textContent = subtotal.toLocaleString() + 'đ';
            totalElement.textContent = (subtotal + 30000).toLocaleString() + 'đ';
        }

        function updateCartQuantity(bookId, change) {
            const item = cart.find(item => item.id === bookId);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromCart(bookId);
                } else {
                    loadCartItems();
                    updateCartBadge();
                }
            }
        }

        function removeFromCart(bookId) {
            const index = cart.findIndex(item => item.id === bookId);
            if (index > -1) {
                cart.splice(index, 1);
                loadCartItems();
                updateCartBadge();
                showToast('Đã xóa sản phẩm khỏi giỏ hàng', 'info');
            }
        }

        function checkout() {
            if (cart.length === 0) {
                showToast('Giỏ hàng trống!', 'warning');
                return;
            }

            const total = cart.reduce((sum, item) => {
                const price = parseInt(item.price.replace(/[^\d]/g, ''));
                return sum + (price * item.quantity);
            }, 0) + 30000;

            showToast(`Đang xử lý đơn hàng ${total.toLocaleString()}đ...`, 'info');

            // Simulate checkout process
            setTimeout(() => {
                cart.length = 0;
                updateCartBadge();
                loadCartItems();
                showToast('Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại BookHub.', 'success');
            }, 2000);
        }

        // Profile page functions
        function showProfileTab(tabName) {
            // First navigate to profile page if not already there
            navigateToPage('profile');

            // Hide all tabs
            document.querySelectorAll('.profile-tab').forEach(tab => {
                tab.style.display = 'none';
            });

            // Remove active class from all nav items
            document.querySelectorAll('.list-group-item').forEach(item => {
                item.classList.remove('active');
            });

            // Show selected tab
            document.getElementById('profile' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).style.display = 'block';

            // Add active class to corresponding nav item
            const navItems = document.querySelectorAll('.list-group-item');
            navItems.forEach(item => {
                if (item.textContent.toLowerCase().includes(tabName === 'info' ? 'thông tin' :
                    tabName === 'orders' ? 'đơn hàng' :
                        tabName === 'wishlist' ? 'yêu thích' : 'cài đặt')) {
                    item.classList.add('active');
                }
            });

            // Load wishlist if wishlist tab is selected
            if (tabName === 'wishlist') {
                loadWishlistItems();
            }
        }

        // Logout function
        function logout() {
            if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
                // Clear user data
                cart.length = 0;
                wishlist.length = 0;
                updateCartBadge();
                updateWishlistBadge();

                // Show logout message
                showToast('Đã đăng xuất thành công!', 'success');

                // Navigate to home
                goHome();
            }
        }

        function loadWishlistItems() {
            const wishlistContainer = document.getElementById('wishlistItems');

            if (wishlist.length === 0) {
                wishlistContainer.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-heart text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">Chưa có sách yêu thích</h5>
                        <p class="text-muted">Hãy thêm những cuốn sách bạn yêu thích</p>
                    </div>
                `;
                return;
            }

            wishlistContainer.innerHTML = '';

            wishlist.forEach(bookId => {
                const book = books.find(b => b.id === bookId);
                if (book) {
                    const wishlistItem = document.createElement('div');
                    wishlistItem.className = 'col-md-6';
                    wishlistItem.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-4">
                                        <div class="book-cover" style="height: 80px; background: ${book.cover}; border-radius: 8px;"></div>
                                    </div>
                                    <div class="col-8">
                                        <h6 class="mb-1">${book.title}</h6>
                                        <small class="text-muted">${book.author}</small>
                                        <div class="mt-2">
                                            <span class="fw-bold text-danger">${book.price}</span>
                                        </div>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary me-2" onclick="addToCart(${book.id})">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="toggleWishlistItem(${book.id}); loadWishlistItems();">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    wishlistContainer.appendChild(wishlistItem);
                }
            });
        }

        // Update navigation system to load cart items when cart page is shown
        const originalNavigateToPage = navigateToPage;
        navigateToPage = function (page) {
            originalNavigateToPage(page);

            if (page === 'cart') {
                loadCartItems();
            }

            // Update product details page
            if (page === 'product') {
                const productId = document.getElementById('productId').value;
                if (productId) {
                    document.getElementById('productTitleDetail').textContent = document.getElementById('productTitle').textContent;
                    document.getElementById('productAuthorDetail').textContent = document.getElementById('productAuthor').textContent;
                }
            }
        };

        // Smooth scrolling for navbar links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    <script>(function () { function c() { var b = a.contentDocument || a.contentWindow.document; if (b) { var d = b.createElement('script'); d.innerHTML = "window.__CF$cv$params={r:'98a64e62620fd176',t:'MTc1OTc2NTc0MC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);"; b.getElementsByTagName('head')[0].appendChild(d) } } if (document.body) { var a = document.createElement('iframe'); a.height = 1; a.width = 1; a.style.position = 'absolute'; a.style.top = 0; a.style.left = 0; a.style.border = 'none'; a.style.visibility = 'hidden'; document.body.appendChild(a); if ('loading' !== document.readyState) c(); else if (window.addEventListener) document.addEventListener('DOMContentLoaded', c); else { var e = document.onreadystatechange || function () { }; document.onreadystatechange = function (b) { e(b); 'loading' !== document.readyState && (document.onreadystatechange = e, c()) } } } })();</script>

@stack('scripts')
