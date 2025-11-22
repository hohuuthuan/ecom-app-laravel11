<?php

namespace App\Http\Controllers\User;

use App\Services\Payments\MomoGateway;
use App\Services\Payments\VnpayGateway;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Province;
use App\Models\Order;
use App\Models\Address;
use App\Models\Warehouse;
use App\Services\User\CartService;
use App\Services\User\CheckoutService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{

    public function enter(Request $request, CartService $svc)
    {
        $raw = (string) $request->input('keys', '');
        $ids = array_values(array_filter(array_unique(explode(',', $raw))));
        if (count($ids) === 0) {
            return redirect()->route('cart')->with('toast_error', 'Bạn cần chọn sản phẩm trước.');
        }

        $cart = $svc->get();
        if (!is_array($cart) || empty($cart['items'])) {
            return redirect()->route('cart')->with('toast_error', 'Các sản phẩm không còn hợp lệ.');
        }

        $cartMap = [];
        foreach ($cart['items'] as $line) {
            if (!empty($line['product_id'])) {
                $cartMap[$line['product_id']] = (int) ($line['qty'] ?? 1);
            }
        }

        $pairs = [];
        foreach ($ids as $pid) {
            if (!isset($cartMap[$pid])) {
                return redirect()->route('cart')->with('toast_error', 'Các sản phẩm không còn hợp lệ');
            }
            $pairs[] = ['id' => $pid, 'qty' => $cartMap[$pid]];
        }

        $exists = Product::query()->whereIn('id', $ids)->pluck('id')->all();
        if (count($exists) !== count($ids)) {
            return redirect()->route('cart')->with('toast_error', 'Các sản phẩm không còn hợp lệ');
        }

        $request->session()->put('checkout.items', $pairs);
        $request->session()->put('checkout.expires_at', now()->addMinutes(15)->timestamp);

        return redirect()->route('checkout.page');
    }

    public function index(Request $request)
    {
        $pairs = $request->session()->get('checkout.items', []);
        if (!is_array($pairs) || count($pairs) === 0) {
            return redirect()->route('cart')->with('toast_error', 'Phiên thanh toán đã hết hạn');
        }

        $ids = [];
        $qtyMap = [];
        foreach ($pairs as $it) {
            if (!empty($it['id'])) {
                $ids[] = $it['id'];
                $qtyMap[$it['id']] = (int) ($it['qty'] ?? 1);
            }
        }

        if (count($ids) === 0) {
            $request->session()->forget(['checkout.items', 'checkout.expires_at']);
            return redirect()->route('cart')->with('toast_error', 'Bạn cần chọn sản phẩm trước');
        }

        $rows = \App\Models\Product::query()
            ->select(['id', 'title', 'image', 'selling_price_vnd'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        if ($rows->count() !== count($ids)) {
            $request->session()->forget(['checkout.items', 'checkout.expires_at']);
            return redirect()->route('cart')->with('toast_error', 'Các sản phẩm đã chọn không còn hợp lệ');
        }

        $items = [];
        $subtotal = 0;
        foreach ($ids as $pid) {
            $p = $rows[$pid];
            $qty = $qtyMap[$pid] ?? 1;
            $price = (int) $p->selling_price_vnd;
            $lineTotal = $price * $qty;

            $items[] = [
                'id'         => (string) $p->id,
                'title'      => (string) $p->title,
                'image'      => (string) ($p->image ?? ''),
                'qty'        => $qty,
                'price'      => $price,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineTotal;
        }

        $shipping = count($items) > 0 ? 30000 : 0;
        $total = $subtotal + $shipping;

        /** @var User|null $user */
        $user = Auth::user();

        $addresses = $user
            ? $user->addresses()
            ->with(['ward', 'province'])
            ->orderByDesc('default')
            ->get()
            : collect();

        $selectedAddress = $addresses->first();

        $fullName = old('full_name', $user?->name ?? '');
        $phone    = $user?->phone ?? '';
        $email    = old('email', $user?->email ?? '');
        $provinces = Province::orderBy('name')->get();

        return view('user.checkout', compact(
            'items',
            'subtotal',
            'shipping',
            'total',
            'addresses',
            'selectedAddress',
            'fullName',
            'phone',
            'email',
            'provinces'
        ));
    }

    protected function generateOrderCode(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $prefix = '';
            for ($i = 0; $i < 2; $i++) {
                $prefix .= $letters[random_int(0, strlen($letters) - 1)];
            }

            $middle = str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
            $suffix = Str::upper(Str::random(10)); // 10 ký tự chữ + số

            // Dạng: AB-12-XXXXXXXXXX
            $code = $prefix . '-' . $middle . '-' . $suffix;
        } while (Order::where('code', $code)->exists());

        return $code;
    }

    protected function isActiveDiscountCode(string $code): bool
    {
        // TODO: implement real logic later
        return true;
    }

    protected function allocateBatchesForItem(string $productId, string $warehouseId, int $quantity): ?array
    {
        if ($quantity <= 0) {
            return [];
        }

        $rows = DB::table('batch_stocks as bs')
            ->join('batches as b', 'b.id', '=', 'bs.batch_id')
            ->where('bs.product_id', $productId)
            ->where('bs.warehouse_id', $warehouseId)
            ->orderBy('b.import_date')
            ->select(
                'bs.batch_id',
                'bs.on_hand',
                'bs.reserved',
                'b.import_price_vnd'
            )
            ->get();

        $need       = $quantity;
        $result     = [];

        foreach ($rows as $row) {
            $available = (int) $row->on_hand - (int) $row->reserved;
            if ($available <= 0) {
                continue;
            }

            $take = $need > $available ? $available : $need;

            $result[] = [
                'batch_id'      => $row->batch_id,
                'quantity'      => $take,
                'unit_cost_vnd' => (int) $row->import_price_vnd,
            ];

            $need -= $take;
            if ($need <= 0) {
                break;
            }
        }

        if ($need > 0) {
            // Không đủ hàng trong các lô
            return null;
        }

        return $result;
    }

    public function placeOrder(Request $request, CheckoutService $checkoutService, CartService $cartService)
    {
        $data = $request->validate([
            'receiver_name'       => ['required', 'string', 'max:255'],
            'receiver_phone'      => ['required', 'string', 'max:20'],
            'email'               => ['nullable', 'email'],
            'shipping_address_id' => ['required', 'string', 'uuid'],
            'payment_method'      => ['required', 'in:cod,momo,vnpay'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*'             => ['required', 'integer', 'min:1'],
            'discount_code'       => ['nullable', 'string', 'max:50'],
            // 'buyer_note'          => ['nullable', 'string', 'max:1000'],
        ]);

        $discountCode = trim((string) ($data['discount_code'] ?? ''));
        if ($discountCode !== '' && !$this->isActiveDiscountCode($discountCode)) {
            return back()
                ->with('toast_error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.')
                ->withInput();
        }

        $user = Auth::user();
        $address = Address::where('id', $data['shipping_address_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return back()
                ->with('toast_error', 'Địa chỉ giao hàng không hợp lệ')
                ->withInput();
        }

        $items      = $data['items']; // items[product_id] = qty
        $productIds = array_keys($items);

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        if ($products->count() !== count($productIds)) {
            return back()
                ->with('toast_error', 'Một hoặc nhiều sản phẩm trong đơn hàng không còn tồn tại')
                ->withInput();
        }

        if ($products->contains(fn($p) => strtoupper($p->status) !== 'ACTIVE')) {
            return back()
                ->with('toast_error', 'Đơn hàng chứa sản phẩm tạm thời ngừng kinh doanh')
                ->withInput();
        }

        $defaultWarehouse = Warehouse::first();
        if (!$defaultWarehouse) {
            return back()
                ->with('toast_error', 'Chưa cấu hình kho hàng, không thể đặt đơn')
                ->withInput();
        }

        // ================== BUILD DATA CHO BẢNG orders ==================
        $itemsCount  = array_sum($items);
        $subtotalVnd = 0;

        foreach ($items as $productId => $qty) {
            /** @var \App\Models\Product $product */
            $product = $products[$productId];

            $productPrice = (int) $product->selling_price_vnd;
            $subtotalVnd += $productPrice * (int) $qty;
        }

        $shippingFeeVnd = 30000; // tạm thời fix 30k
        $discountVnd    = 0;     // chưa áp mã giảm giá vào tiền
        $taxVnd         = 0;     // chưa dùng VAT

        $grandTotalVnd = $subtotalVnd - $discountVnd + $taxVnd + $shippingFeeVnd;

        $orderId = (string) Str::uuid();

        $orderData = [
            'id'                => $orderId,
            'code'              => $this->generateOrderCode(),
            'user_id'           => $user->id,
            'status'            => 'pending',
            'payment_method'    => 'cod',
            'payment_status'    => 'unpaid',
            'items_count'       => $itemsCount,
            'subtotal_vnd'      => $subtotalVnd,
            'discount_vnd'      => $discountVnd,
            'tax_vnd'           => $taxVnd,
            'shipping_fee_vnd'  => $shippingFeeVnd,
            'grand_total_vnd'   => $grandTotalVnd,
            'discount_id'       => null, // sau này map từ bảng discounts theo $discountCode
            'buyer_note'        => $request->input('buyer_note') ?? null,
            'placed_at'         => now(),
            'delivered_at'      => null,
            'cancelled_at'      => null,
        ];

        // ================== BUILD DATA CHO BẢNG order_items ==================
        $orderItemsData = [];

        foreach ($items as $productId => $qty) {
            /** @var \App\Models\Product $product */
            $product = $products[$productId];

            $quantity  = (int) $qty;
            $unitPrice = (int) $product->selling_price_vnd;

            $discountAmountVnd   = 0;    // chưa chia giảm giá theo từng dòng
            $taxRate             = null; // chưa dùng VAT
            $taxAmountVnd        = 0;
            $unitCostSnapshotVnd = 0;    // TODO: sau này tính từ order_batches
            $totalPriceVnd       = $quantity * $unitPrice - $discountAmountVnd + $taxAmountVnd;

            $orderItemsData[] = [
                'id'                       => (string) Str::uuid(),
                'order_id'                 => $orderId,
                'product_id'               => $productId,
                'product_title_snapshot'   => $product->title,
                'isbn13_snapshot'          => $product->isbn ?? null,
                'warehouse_id'             => $defaultWarehouse->id,
                'quantity'                 => $quantity,
                'unit_price_vnd'           => $unitPrice,
                'discount_amount_vnd'      => $discountAmountVnd,
                'tax_rate'                 => $taxRate,
                'tax_amount_vnd'           => $taxAmountVnd,
                'unit_cost_snapshot_vnd'   => $unitCostSnapshotVnd,
                'total_price_vnd'          => $totalPriceVnd,
                'created_at'               => now(),
                'updated_at'               => now(),
            ];
        }

        // ================== BUILD DATA CHO BẢNG shipments ==================
        $shipmentAddress = $address->address;

        if ($address->ward || $address->province) {
            $parts = [$address->address];

            if ($address->ward) {
                $parts[] = $address->ward->name;
            }

            if ($address->province) {
                $parts[] = $address->province->name;
            }

            $shipmentAddress = implode(', ', $parts);
        }

        $shipmentData = [
            'id'           => (string) Str::uuid(),
            'order_id'     => $orderId,
            'status'       => 'pending',
            'name'         => $data['receiver_name'],
            'phone'        => $data['receiver_phone'],
            'email'        => $data['email'] ?? null,
            'address'      => $shipmentAddress,
            'picked_at'    => null,
            'shipped_at'   => null,
            'delivered_at' => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        // ================== BUILD DATA CHO BẢNG order_batches ==================
        $orderBatchesData = [];

        foreach ($orderItemsData as $itemRow) {
            $allocations = $this->allocateBatchesForItem(
                $itemRow['product_id'],
                $itemRow['warehouse_id'],
                $itemRow['quantity']
            );

            if ($allocations === null) {
                $productTitle = $products[$itemRow['product_id']]->title ?? 'Sản phẩm';
                return back()
                    ->with('toast_error', 'Sản phẩm "' . $productTitle . '" không đủ tồn kho theo lô hàng.')
                    ->withInput();
            }

            foreach ($allocations as $alloc) {
                $orderBatchesData[] = [
                    'order_item_id' => $itemRow['id'],
                    'batch_id'      => $alloc['batch_id'],
                    'quantity'      => $alloc['quantity'],
                    'unit_cost_vnd' => $alloc['unit_cost_vnd'],
                ];
            }
        }

        if ($data['payment_method'] === 'momo') {
            $orderData['payment_method'] = 'momo';
            $orderData['payment_status'] = 'pending';

            $request->session()->put('checkout.momo_pending', [
                'orderData'       => $orderData,
                'orderItemsData'  => $orderItemsData,
                'shipmentData'    => $shipmentData,
                'orderBatchesData' => $orderBatchesData,
                'items'           => $items,
            ]);

            $gateway = new MomoGateway(config('payment.momo'));
            $payUrl  = $gateway->createPaymentUrl(
                $orderData['code'],
                (int) $orderData['grand_total_vnd'],
                'Thanh toán đơn hàng #' . $orderData['code']
            );

            return redirect()->away($payUrl);
        }

        if ($data['payment_method'] === 'vnpay') {
            $orderData['payment_method'] = 'vnpay';
            $orderData['payment_status'] = 'pending';

            $request->session()->put('checkout.vnpay_pending', [
                'orderData'        => $orderData,
                'orderItemsData'   => $orderItemsData,
                'shipmentData'     => $shipmentData,
                'orderBatchesData' => $orderBatchesData,
                'items'            => $items,
            ]);

            $gateway = new VnpayGateway(config('payment.vnpay'));
            $payUrl  = $gateway->createPaymentUrl(
                $orderData['code'],
                (int) $orderData['grand_total_vnd'],
                'Thanh toán đơn hàng #' . $orderData['code'],
                $request->ip()
            );

            return redirect()->away($payUrl);
        }
        if ($data['payment_method'] === 'cod') {
            $order = $checkoutService->placeCodOrder(
                $orderData,
                $orderItemsData,
                $shipmentData,
                $orderBatchesData
            );

            if ($order) {
                $request->session()->forget(['checkout.items', 'checkout.expires_at']);

                foreach (array_keys($items) as $productId) {
                    $cartService->removeItemInCart((string) $productId);
                }

                return redirect()
                    ->route('user.thanks', ['code' => $order->code])
                    ->with('toast_success', 'Đặt hàng thành công');
            }

            return back()
                ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function momoReturn(Request $request, CheckoutService $checkoutService, CartService $cartService)
    {
        $pending = $request->session()->get('checkout.momo_pending');

        if (!$pending) {
            return redirect()
                ->route('cart')
                ->with('toast_error', 'Không tìm thấy thông tin đơn hàng chờ thanh toán.');
        }

        $resultCode = (int) $request->input('resultCode', -1);
        if ($resultCode !== 0) {
            $request->session()->forget('checkout.momo_pending');

            return redirect()
                ->route('checkout.page')
                ->with('toast_error', 'Thanh toán MoMo thất bại hoặc bị hủy.');
        }

        $orderData        = $pending['orderData'];
        $orderItemsData   = $pending['orderItemsData'];
        $shipmentData     = $pending['shipmentData'];
        $orderBatchesData = $pending['orderBatchesData'];
        $items            = $pending['items'];

        // Cập nhật trạng thái thanh toán trước khi insert
        $orderData['payment_method'] = 'momo';
        $orderData['payment_status'] = 'paid';

        $order = $checkoutService->placeCodOrder(
            $orderData,
            $orderItemsData,
            $shipmentData,
            $orderBatchesData
        );

        $request->session()->forget('checkout.momo_pending');
        $request->session()->forget(['checkout.items', 'checkout.expires_at']);

        if ($order) {
            foreach (array_keys($items) as $productId) {
                $cartService->removeItemInCart((string) $productId);
            }

            return redirect()
                ->route('user.thanks', ['code' => $order->code])
                ->with('toast_success', 'Thanh toán MoMo thành công, đơn hàng đã được tạo.');
        }

        return redirect()
            ->route('checkout.page')
            ->with('toast_error', 'Có lỗi xảy ra khi tạo đơn sau khi thanh toán MoMo.');
    }

    public function vnpayReturn(Request $request, CheckoutService $checkoutService, CartService $cartService)
    {
        $pending = $request->session()->get('checkout.vnpay_pending');

        if (!$pending) {
            return redirect()
                ->route('cart')
                ->with('toast_error', 'Không tìm thấy thông tin đơn hàng chờ thanh toán VNPAY.');
        }

        // ==== VERIFY CHỮ KÝ VNPAY ====
        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'vnp_') === 0) {
                $inputData[$key] = $value;
            }
        }

        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashParts = [];
        foreach ($inputData as $key => $value) {
            $hashParts[] = urlencode($key) . '=' . urlencode((string) $value);
        }
        $hashData = implode('&', $hashParts);

        $hashSecret = (string) config('payment.vnpay.hash_secret');
        $calcHash   = hash_hmac('sha512', $hashData, $hashSecret);

        if ($calcHash !== $vnpSecureHash) {
            Log::warning('VNPay invalid signature', [
                'hashData'      => $hashData,
                'calcHash'      => $calcHash,
                'vnpSecureHash' => $vnpSecureHash,
            ]);

            $request->session()->forget('checkout.vnpay_pending');

            return redirect()
                ->route('checkout.page')
                ->with('toast_error', 'Chữ ký VNPAY không hợp lệ.');
        }


        // ==== CHECK KẾT QUẢ THANH TOÁN ====
        $responseCode = $request->input('vnp_ResponseCode');
        if ($responseCode !== '00') {
            $request->session()->forget('checkout.vnpay_pending');

            return redirect()
                ->route('checkout.page')
                ->with('toast_error', 'Thanh toán VNPAY thất bại hoặc đã bị hủy.');
        }

        $orderData        = $pending['orderData'];
        $orderItemsData   = $pending['orderItemsData'];
        $shipmentData     = $pending['shipmentData'];
        $orderBatchesData = $pending['orderBatchesData'];
        $items            = $pending['items'];

        $orderData['payment_method'] = 'vnpay';
        $orderData['payment_status'] = 'paid';

        $order = $checkoutService->placeCodOrder(
            $orderData,
            $orderItemsData,
            $shipmentData,
            $orderBatchesData
        );

        $request->session()->forget('checkout.vnpay_pending');
        $request->session()->forget(['checkout.items', 'checkout.expires_at']);

        if ($order) {
            foreach (array_keys($items) as $productId) {
                $cartService->removeItemInCart((string) $productId);
            }

            return redirect()
                ->route('user.thanks', ['code' => $order->code])
                ->with('toast_success', 'Thanh toán VNPAY thành công, đơn hàng đã được tạo.');
        }

        return redirect()
            ->route('checkout.page')
            ->with('toast_error', 'Có lỗi xảy ra khi tạo đơn sau khi thanh toán VNPAY.');
    }
}
