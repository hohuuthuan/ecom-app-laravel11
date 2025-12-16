<?php

namespace App\Http\Controllers\User\Page;

use App\Models\Product;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\ProductService;
use App\Services\User\HomeService;
use App\Services\User\CartService;
use Illuminate\Contracts\View\View;

class HomePageController extends Controller
{
  public function __construct(
    private ProductService $productService,
    private HomeService $homeService,
    private CartService $cartService
  ) {}

  public function index(Request $r)
  {
    $filters = [
      'per_page'      => (int)$r->query('per_page_product', 9),
      'keyword'       => $r->query('keyword'),
      'status'        => $r->query('status'),
      'category_id'   => $r->query('category_id'),
      'author_id'     => $r->query('author_id'),
      'publisher_id'  => $r->query('publisher_id'),
      'price_min'     => $r->query('price_min'),
      'price_max'     => $r->query('price_max'),
      'stock_min'     => $r->query('stock_min'),
      'stock_max'     => $r->query('stock_max'),
      'sort' => 'best_seller',
    ];

    $categories = $this->productService->getListCategory();
    $authors    = $this->productService->getListAuthor();
    $publishers = $this->productService->getListPublisher();

    $products   = $this->productService->getList($filters);

    return view('user.home', compact('categories', 'authors', 'publishers', 'products'));
  }

  public function listFavoriteProduct()
  {
    $categories = $this->productService->getListCategory();
    $authors    = $this->productService->getListAuthor();
    $publishers = $this->productService->getListPublisher();
    $products = $this->homeService->getListFavoriteProduct();

    return view('user.favoriteProduct', compact('categories', 'authors', 'publishers', 'products'));
  }

  public function recentlyViewedPage(Request $request): View
  {
    $raw = (string) $request->cookie('recently_viewed_products', '[]');

    try {
      $data = json_decode($raw, true);
    } catch (\Throwable $e) {
      $data = [];
    }

    if (!is_array($data)) {
      $data = [];
    }

    $items = array_values(array_filter($data, function ($item) {
      return is_array($item)
        && array_key_exists('id', $item)
        && is_string($item['id'])
        && $item['id'] !== ''
        && array_key_exists('viewed_at', $item)
        && is_string($item['viewed_at'])
        && $item['viewed_at'] !== '';
    }));

    $ids = array_map(function (array $item) {
      return (string) $item['id'];
    }, $items);

    $perPage = (int) $request->query('per_page_product', 9);
    if ($perPage <= 0) {
      $perPage = 9;
    }
    if ($perPage > 200) {
      $perPage = 200;
    }

    $products = $this->productService->getRecentlyViewedProducts($ids, $perPage);

    return view('user.recentlyViewed', [
      'products' => $products,
    ]);
  }

  public function productDetail(Request $request)
  {
    $id = (string) $request->route('id');

    $product = $this->productService->getProductDetail($id);
    if (!$product) {
      return back()->with('toast_error', 'Không tìm thấy sản phẩm');
    }

    $perPageReview = (int) $request->query('per_page_review', 4);
    if ($perPageReview <= 0 || $perPageReview > 200) {
      $perPageReview = 4;
    }

    $perPageRelated = (int) $request->query('per_page_related', 6);
    if ($perPageRelated <= 0 || $perPageRelated > 200) {
      $perPageRelated = 6;
    }

    if ($request->ajax() && $request->boolean('reviews_only')) {
      $reviews = $this->productService->getProductReviews($id, $perPageReview);

      return view('partials.ui.productDetail.reviews-list', [
        'reviews' => $reviews,
      ]);
    }

    if ($request->ajax() && $request->boolean('related_only')) {
      $relatedProducts = $this->productService->getRelatedProducts($product, $perPageRelated);

      return view('partials.ui.productDetail.related-products', [
        'products'       => $relatedProducts,
        'perPageRelated' => $perPageRelated,
      ]);
    }

    $reviews         = $this->productService->getProductReviews($id, $perPageReview);
    $relatedProducts = $this->productService->getRelatedProducts($product, $perPageRelated);

    // ================== Recently viewed cookie ==================
    $raw = (string) $request->cookie('recently_viewed_products', '[]');

    try {
      $items = json_decode($raw, true);
    } catch (\Throwable $e) {
      $items = [];
    }

    if (!is_array($items)) {
      $items = [];
    }

    // Chỉ giữ item có id dạng string không rỗng
    $items = array_values(array_filter($items, function ($item) {
      return is_array($item)
        && array_key_exists('id', $item)
        && is_string($item['id'])
        && $item['id'] !== '';
    }));

    $now         = now()->toIso8601String();
    $exists      = false;
    $updatedItems = [];

    foreach ($items as $item) {
      $itemId = (string) $item['id'];

      if ($itemId === (string) $product->id) {
        // Đã xem trước đó -> chỉ update thời gian xem
        $item['viewed_at'] = $now;
        $exists = true;
      }

      $updatedItems[] = [
        'id'        => $itemId,
        'viewed_at' => isset($item['viewed_at']) && is_string($item['viewed_at'])
          ? $item['viewed_at']
          : $now,
      ];
    }

    if (!$exists) {
      // Chưa từng xem -> thêm mới (mặc định mới nhất, sẽ sort lại phía dưới)
      $updatedItems[] = [
        'id'        => (string) $product->id,
        'viewed_at' => $now,
      ];
    }

    // Sắp xếp theo thời gian xem MỚI NHẤT trước (để vào trang lịch sử thấy ngay sản phẩm vừa xem)
    usort($updatedItems, function (array $a, array $b): int {
      return strcmp($b['viewed_at'], $a['viewed_at']);
    });

    $maxItems = 50;
    if (count($updatedItems) > $maxItems) {
      $updatedItems = array_slice($updatedItems, 0, $maxItems);
    }

    $minutes = 60 * 24 * 30;
    cookie()->queue('recently_viewed_products', json_encode($updatedItems), $minutes);
    // ================== END Recently viewed cookie ==================

    return view('user.productDetail', [
      'product'         => $product,
      'reviews'         => $reviews,
      'perPageReview'   => $perPageReview,
      'perPageRelated'  => $perPageRelated,
      'relatedProducts' => $relatedProducts,
    ]);
  }

  public function cartPage(Request $request, CartService $svc)
  {
    $cart = $svc->recalc();
    return view('user.cart', compact('cart'));
  }

  public function countProductInCart(CartService $svc)
  {
    return response()->json(['count' => $svc->countDistinct()]);
  }

  public function addItemToCart(Request $request, CartService $svc)
  {
    $data = $request->validate([
      'product_id' => ['required', 'exists:products,id'],
      'qty'        => ['nullable', 'integer', 'min:1'],
    ]);

    $qty = (int) ($data['qty'] ?? 1);

    $p = Product::query()
      ->select('id', 'title')
      ->with('stocks:product_id,on_hand')
      ->find($data['product_id']);

    if (!$p) {
      if ($request->ajax() || $request->expectsJson()) {
        return response()->json(['ok' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
      }
      return back()->with('toast_error', 'Sản phẩm không tồn tại');
    }

    $available = (int) $p->stocks->sum(function ($s) {
      return (int) $s->on_hand;
    });

    if ($available <= 0) {
      if ($request->ajax() || $request->expectsJson()) {
        return response()->json(['ok' => false, 'message' => 'Sản phẩm tạm hết hàng'], 409);
      }
      return back()->with('toast_error', 'Sản phẩm tạm hết hàng');
    }

    $cart = $svc->get();
    $key  = $data['product_id'];
    $curr = isset($cart['items'][$key]) ? (int) $cart['items'][$key]['qty'] : 0;

    $target = ($curr + $qty) > $available ? $available : ($curr + $qty);
    $addQty = $target - $curr;

    if ($addQty > 0) {
      $svc->add($data['product_id'], null, $addQty);
    }

    if ($request->ajax() || $request->expectsJson()) {
      return response()->json([
        'ok'          => true,
        'count'       => $svc->countDistinct(),
        'qty_in_cart' => $target,
        'max_qty'     => $available,
        'reached_max' => $target >= $available,
        'message'     => $addQty > 0 ? 'Đã thêm vào giỏ' : ('Đã đạt số lượng tối đa (còn ' . $available . ')'),
      ], 200);
    }

    if ($addQty > 0) {
      return back()->with('toast_success', 'Đã thêm sản phẩm vào giỏ hàng');
    }

    return back()->with('toast_info', 'Đã đạt số lượng tối đa (còn ' . $available . ')');
  }


  public function updateQuantityItemInCart(string $key, Request $request, CartService $svc)
  {
    $request->validate(['qty' => 'required|integer|min:1']);
    $cart = $svc->updateQuantityItemInCart($key, (int)$request->qty);
    if ($request->ajax() || $request->expectsJson()) {
      return response()->json([
        'ok' => true,
        'count' => $svc->countDistinct(),
        'cart' => $cart
      ], 200);
    }
    return back();
  }

  public function removeItemInCart(string $key, Request $request)
  {
    $cart = $this->cartService->removeItemInCart($key);
    if ($request->ajax() || $request->expectsJson()) {
      return response()->json([
        'ok' => true,
        'count' => $this->cartService->countDistinct(),
        'cart' => $cart
      ], 200);
    }

    return back()->with('toast_success', 'Đã xoá sản phẩm khỏi giỏ hàng');
  }

  public function clearCart(Request $request)
  {
    $cart = $this->cartService->recalc();

    if (!empty($cart['items']) && is_array($cart['items'])) {
      foreach ($cart['items'] as $line) {
        if (!empty($line['key'])) {
          $this->cartService->removeItemInCart((string) $line['key']);
        }
      }
    }

    $cart = $this->cartService->recalc();

    if ($request->ajax() || $request->expectsJson()) {
      return response()->json([
        'ok'    => true,
        'count' => $this->cartService->countDistinct(),
        'cart'  => $cart,
      ]);
    }

    return back()->with('toast_success', 'Đã xóa tất cả sản phẩm khỏi giỏ hàng');
  }

  public function thanks(Request $request)
  {
    $code = $request->query('code');

    $order = null;
    if ($code) {
      $order = Order::where('code', $code)->first();
    }

    return view('user.thanks', [
      'order' => $order,
    ]);
  }

  public function listProduct(Request $request)
  {
    $filters = [
      'per_page'     => (int)$request->query('per_page_product', 9),
      'keyword'      => $request->query('keyword'),
      'status'       => $request->query('status'),
      'category_id'  => $request->query('category_id'),
      'author_id'    => $request->query('author_id'),
      'publisher_id' => $request->query('publisher_id'),
      'price_min'    => $request->query('price_min'),
      'price_max'    => $request->query('price_max'),
      'sort_by'      => $request->query('sort_by'),
    ];

    $categories = $this->productService->getListCategory();
    $authors    = $this->productService->getListAuthor();
    $publishers = $this->productService->getListPublisher();
    $products   = $this->productService->listProduct($filters);
    $maxPrice   = $this->productService->getMaxSellingPrice();

    return view('user.listProduct', compact('categories', 'authors', 'publishers', 'products', 'maxPrice'));
  }
}
