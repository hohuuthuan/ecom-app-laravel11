<?php

namespace App\Http\Controllers\User\Page;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\ProductService;
use App\Services\User\HomeService;
use App\Services\User\CartService;

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
      'per_page'      => (int)$r->query('per_page_product', 10),
      'keyword'       => $r->query('keyword'),
      'status'        => $r->query('status'),
      'category_id'   => $r->query('category_id'),
      'author_id'     => $r->query('author_id'),
      'publisher_id'  => $r->query('publisher_id'),
      'price_min'     => $r->query('price_min'),
      'price_max'     => $r->query('price_max'),
      'stock_min'     => $r->query('stock_min'),
      'stock_max'     => $r->query('stock_max'),
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

  public function productDetail(Request $request)
  {
    $id = (string) $request->route('id');
    $product = $this->productService->getProductDetail($id);
    if (!$product) {
      return back()->with('toast_error', 'Không tìm thấy sản phẩm');
    }

    return view('user.productDetail', compact('product'));
  }

  public function cartPage(CartService $svc)
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

    $qty = (int)($data['qty'] ?? 1);

    $p = Product::query()
      ->select('id', 'title')
      ->with('stocks:product_id,on_hand,reserved')
      ->find($data['product_id']);
    if (!$p) {
      if ($request->ajax() || $request->expectsJson()) {
        return response()->json(['ok' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
      }
      return back()->with('toast_error', 'Sản phẩm không tồn tại');
    }

    $available = (int)$p->stocks->sum(function ($s) {
      return (int)$s->on_hand - (int)$s->reserved;
    });
    if ($available <= 0) {
      if ($request->ajax() || $request->expectsJson()) {
        return response()->json(['ok' => false, 'message' => 'Sản phẩm tạm hết hàng'], 409);
      }
      return back()->with('toast_error', 'Sản phẩm tạm hết hàng');
    }

    $cart  = $svc->get();
    $key   = $data['product_id'];
    $curr  = isset($cart['items'][$key]) ? (int)$cart['items'][$key]['qty'] : 0;
    $need  = $curr + $qty;
    if ($need > $available) {
      if ($request->ajax() || $request->expectsJson()) {
        return response()->json(['ok' => false, 'message' => 'Số lượng vượt quá tồn kho'], 422);
      }
      return back()->with('toast_error', 'Số lượng vượt quá tồn kho (còn ' . $available . ')');
    }

    $svc->add($data['product_id'], null, $qty);
    if ($request->ajax() || $request->expectsJson()) {
      return response()->json(['ok' => true, 'count' => $svc->countDistinct()], 200);
    }

    return back()->with('toast_success', 'Đã thêm sản phẩm vào giỏ hàng');
  }

  public function updateQuantityItemInCart(string $key, Request $request, CartService $svc)
  {
    $request->validate(['qty' => 'required|integer|min:1']);
    $svc->updateQuantityItemInCart($key, (int)$request->qty);
    return back();
  }

  public function removeItemInCart(string $key, Request $request, CartService $svc) // Thêm Request
  {
    $cart = $svc->removeItemInCart($key);

    if ($request->ajax() || $request->expectsJson()) {
      return response()->json([
        'ok' => true,
        'count' => $svc->countDistinct(),
        'cart' => $cart
      ], 200);
    }

    return back()->with('toast_success', 'Đã xoá sản phẩm khỏi giỏ hàng');
  }
}
