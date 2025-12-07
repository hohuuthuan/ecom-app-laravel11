<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\User\Page\HomePageController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\UserAddressController;
use App\Http\Controllers\User\UserOrderController;
use App\Http\Controllers\User\UserReviewController;

use App\Http\Controllers\Admin\Page\CatalogPageController;
use App\Http\Controllers\Admin\Page\ProductPageController;
use App\Http\Controllers\Admin\Page\OrderPageController;
use App\Http\Controllers\Admin\Page\WarehousePageController;
use App\Http\Controllers\Admin\Page\DiscountPageController;
use App\Http\Controllers\Admin\Page\ReviewPageController;

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DiscountController;

// === PUBLIC ===
Route::get('/', [HomePageController::class, 'index'])->name('home');
Route::get('/product', [HomePageController::class, 'listProduct'])->name('product.list');
Route::get('/product/{slug}/{id}', [HomePageController::class, 'productDetail'])->name('product.detail');
Route::get('/cart', [HomePageController::class, 'cartPage'])->name('cart');
Route::post('/cart/add', [HomePageController::class, 'addItemToCart'])->name('cart.item.add');
Route::patch('/cart/item/{key}', [HomePageController::class, 'updateQuantityItemInCart'])->name('cart.item.update');
Route::delete('/cart/item/{key}', [HomePageController::class, 'removeItemInCart'])->name('cart.item.remove');
Route::get('/cart/count', [HomePageController::class, 'countProductInCart'])->name('cart.count');
Route::delete('/cart/clear', [HomePageController::class, 'clearCart'])->name('cart.clear');
Route::get('/thank-you', [HomePageController::class, 'thanks'])->name('user.thanks');

// === GUEST ===
Route::middleware('guest')->group(function () {
  Route::view('/login', 'auth.login')->name('login.form');
  Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:10,1');

  Route::view('/register', 'auth.register')->name('register.form');
  Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// === LOGGED IN ===
Route::middleware(['auth'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  Route::get('/favorite', [HomePageController::class, 'listFavoriteProduct'])->name('listFavoriteProduct');
  Route::post('/favorite', [HomeController::class, 'addFavoriteProduct'])->name('addFavoriteProduct');
  Route::delete('/favorite/{productId}', [HomeController::class, 'destroyFavoriteProduct'])->name('destroyFavoriteProduct');

  Route::post('/checkout', [CheckoutController::class, 'enter'])->name('checkout.index');
  Route::get('/checkout', [CheckoutController::class, 'index'])->middleware(['checkout.valid'])->name('checkout.page');
  Route::post('/checkout/placeOrder', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');
  Route::get('/checkout/momo/return', [CheckoutController::class, 'momoReturn'])->name('checkout.momoReturn');
  Route::get('/checkout/vnpay/return', [CheckoutController::class, 'vnpayReturn'])->name('checkout.vnpayReturn');
  Route::post('/checkout/apply-discount', [CheckoutController::class, 'applyDiscount'])->name('checkout.applyDiscount');
  Route::delete('/checkout/discount', [CheckoutController::class, 'removeDiscount'])->name('checkout.removeDiscount');

  Route::get('/orders/{id}/review', [UserReviewController::class, 'showOrderReview'])->whereUuid('id')->name('user.reviews.order');
  Route::post('/profile/orders/reviews/store-item', [UserReviewController::class, 'storeFromOrder'])->name('user.reviews.storeFromOrder');

  Route::prefix('profile')->name('user.profile.')->group(function () {
    Route::get('/', [UserAddressController::class, 'index'])->name('index');
    Route::get('/wards', [UserAddressController::class, 'getWards'])->name('wards');

    Route::put('/info', [UserAddressController::class, 'updateInfo'])->name('info.update');

    Route::post('/address', [UserAddressController::class, 'storeNewAddress'])->name('storeNewAddress');
    Route::put('/address/{id}', [UserAddressController::class, 'updateAddress'])->name('updateAddress');
    Route::delete('/address/{id}', [UserAddressController::class, 'destroyAddress'])->name('destroyAddress');
    Route::put('/address/{id}/default', [UserAddressController::class, 'setAddressDefault'])->name('setAddressDefault');

    Route::get('/orders/{id}', [UserOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{id}/review', [UserReviewController::class, 'showOrderReviewPage'])->name('orders.review');
  });

  Route::prefix('admin')->as('admin.')->middleware('role:Admin')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');

    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts/bulk-update', [AccountController::class, 'bulkUpdate'])->name('accounts.bulk-update');
    Route::put('/accounts/{id}', [AccountController::class, 'updateAccount'])->name('accounts.update');

    Route::get('/catalog', [CatalogPageController::class, 'index'])->name('catalog.index');
    Route::post('/authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::put('/authors/{id}', [AuthorController::class, 'update'])->name('authors.update');
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy'])->name('authors.destroy');
    Route::delete('/authors/bulk-delete', [AuthorController::class, 'bulkDelete'])->name('authors.bulk-delete');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::delete('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::post('/publishers', [PublisherController::class, 'store'])->name('publishers.store');
    Route::put('/publishers/{id}', [PublisherController::class, 'update'])->name('publishers.update');
    Route::delete('/publishers/{id}', [PublisherController::class, 'destroy'])->name('publishers.destroy');
    Route::post('/publishers/bulk-delete', [PublisherController::class, 'bulkDelete'])->name('publishers.bulk-delete');

    Route::get('/product', [ProductPageController::class, 'index'])->name('product.index');
    Route::get('/product/create', [ProductPageController::class, 'create'])->name('product.create');
    Route::get('/products/{id}/edit',  [ProductPageController::class, 'edit'])->name('product.edit');

    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('product.update');

    Route::get('/order', [OrderPageController::class, 'index'])->name('order.index');
    Route::get('/order/detail/{id}', [OrderPageController::class, 'detail'])->name('order.detail');
    Route::patch('/order/{id}/change-status', [OrderPageController::class, 'changeStatus'])->name('order.changeStatus');

    Route::get('/discounts', [DiscountPageController::class, 'index'])->name('discount.index');
    Route::get('/discounts/create', [DiscountPageController::class, 'create'])->name('discount.create');
    Route::get('/discounts/{id}', [DiscountPageController::class, 'show'])->name('discount.show');
    Route::get('/discounts/{id}/edit', [DiscountPageController::class, 'edit'])->name('discount.edit');
    Route::post('/discounts', [DiscountController::class, 'store'])->name('discount.store');
    Route::put('/discounts/{id}', [DiscountController::class, 'update'])->name('discount.update');
    Route::delete('/discounts/{id}', [DiscountController::class, 'destroy'])->name('discount.destroy');

    // Route::get('review', [ReviewPageController::class, 'index'])->name('review.index');
    // Route::get('review/product/{productId}', [ReviewPageController::class, 'show'])->name('review.product.show');

    Route::get('review', [ReviewPageController::class, 'index'])->name('review.index');
    Route::get('review/product/{productId}', [ReviewPageController::class, 'show'])->name('review.product.show');
    Route::patch('review/{id}', [ReviewPageController::class, 'updateReview'])->name('review.update');
  });

  Route::middleware(['auth', 'role:Admin,Warehouse Manager'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/', [WarehousePageController::class, 'dashboard'])->name('dashboard');

    Route::get('/orders', [WarehousePageController::class, 'orders'])->name('orders');
    Route::get('/orders/detail/{id}', [WarehousePageController::class, 'orderDetail'])->name('order.detail');
    Route::patch('/orders/{id}/status', [WarehousePageController::class, 'changeOrderStatus'])->name('order.changeStatus');

    Route::get('/inventory', [WarehousePageController::class, 'inventory'])->name('inventory');

    Route::get('/import', [WarehousePageController::class, 'import'])->name('import');
    Route::get('/import/products', [WarehousePageController::class, 'productsByPublisher'])->name('import.products');
    Route::post('/import', [WarehousePageController::class, 'handleImport'])->name('import.handle');
    Route::get('/purchase-receipts', [WarehousePageController::class, 'purchaseReceiptIndex'])->name('purchase_receipts.index');
    Route::get('/purchase-receipts/{id}', [WarehousePageController::class, 'purchaseReceiptShow'])->name('purchase_receipts.show');
  });
});

Route::fallback(fn() => response()->view('errors.404', [], 404));
