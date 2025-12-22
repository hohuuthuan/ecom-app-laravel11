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
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\DiscountWalletItem;
use App\Services\User\CartService;
use App\Services\User\CheckoutService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;

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

        $fullName = old('receiver_name', $user?->name ?? '');
        $phone    = old('receiver_phone', $user?->phone ?? '');
        $email    = old('email', $user?->email ?? '');
        $provinces = Province::orderBy('name')->get();

        $profileComplete = $user
            ? (trim((string) $user->name) !== '' && trim((string) ($user->phone ?? '')) !== '')
            : false;

        $walletDiscounts = $user
            ? DiscountWalletItem::query()
            ->with(['discount:id,code,type,value,min_order_value_vnd,usage_limit,per_user_limit,start_date,end_date,status'])
            ->where('user_id', $user->id)
            ->where('status', 'SAVED')
            ->orderByDesc('created_at')
            ->get()
            : collect();


        return view('user.checkout', compact(
            'items',
            'subtotal',
            'shipping',
            'total',
            'user',
            'addresses',
            'selectedAddress',
            'fullName',
            'phone',
            'email',
            'provinces',
            'walletDiscounts',
            'profileComplete'
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

    protected function hasEnoughStockForItem(string $productId, int $quantity): bool
    {
        $quantity = (int) $quantity;

        if ($quantity <= 0) {
            return false;
        }

        $totalOnHand = DB::table('stocks')
            ->where('product_id', $productId)
            ->selectRaw('COALESCE(SUM(on_hand), 0) as total_on_hand')
            ->value('total_on_hand');

        return (int) $totalOnHand >= $quantity;
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
                'b.import_price_vnd'
            )
            ->get();

        $need = $quantity;
        $result = [];

        foreach ($rows as $row) {
            $available = (int) $row->on_hand;
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
            return null;
        }

        return $result;
    }

    public function placeOrder(Request $request, CheckoutService $checkoutService, CartService $cartService)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $buyerInfo = $checkoutService->getBuyerInfo($user);
        if (!empty($buyerInfo['errors'])) {
            return redirect()
                ->route('checkout.page')
                ->with('toast_error', 'Bạn cần cập nhật đầy đủ họ tên và số điện thoại trước khi đặt hàng.')
                ->with('checkout_open_profile_modal', true);
        }

        $data = $request->validate([
            'email'               => ['nullable', 'email'],
            'shipping_address_id' => ['required', 'string', 'uuid'],
            'payment_method'      => ['required', 'in:cod,momo,vnpay'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*'             => ['required', 'integer', 'min:1'],
            'discount_code'       => ['nullable', 'string', 'max:50'],
        ]);

        $data['receiver_name'] = $buyerInfo['name'];
        $data['receiver_phone'] = $buyerInfo['phone'];

        $address = Address::where('id', $data['shipping_address_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return back()
                ->with('toast_error', 'Địa chỉ giao hàng không hợp lệ')
                ->withInput();
        }

        $items = $data['items']; // items[product_id] = qty
        $productIds = array_keys($items);

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        if ($products->count() !== count($productIds)) {
            return back()
                ->with('toast_error', 'Một hoặc nhiều sản phẩm trong đơn hàng không còn tồn tại')
                ->withInput();
        }

        if ($products->contains(function ($p) {
            return strtoupper((string) $p->status) !== 'ACTIVE';
        })) {
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

        foreach ($items as $productId => $qty) {
            $quantity = (int) $qty;
            $product = $products[$productId];

            if (!$this->hasEnoughStockForItem((string) $productId, (int) $quantity)) {
                return back()
                    ->with('toast_error', 'Sản phẩm "' . $product->title . '" không đủ tồn kho.')
                    ->withInput();
            }
        }

        $itemsCount = array_sum($items);
        $subtotalVnd = 0;

        foreach ($items as $productId => $qty) {
            $product = $products[$productId];
            $subtotalVnd += ((int) $product->selling_price_vnd) * (int) $qty;
        }

        $shippingBaseVnd = $itemsCount > 0 ? 30000 : 0;
        $taxVnd = 0;

        $discountCode = strtoupper(trim((string) ($data['discount_code'] ?? '')));
        $discountVnd = 0;
        $shippingDiscountVnd = 0;
        $discountId = null;
        $discountType = null;

        if ($discountCode === '') {
            $request->session()->forget('checkout_discount');
        } else {
            $discount = Discount::query()
                ->where('code', $discountCode)
                ->where('status', 'ACTIVE')
                ->first();

            if (!$discount) {
                $request->session()->forget('checkout_discount');
                return back()
                    ->with('toast_error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.')
                    ->withInput();
            }

            if ($discount->start_date && $discount->start_date->isFuture()) {
                $request->session()->forget('checkout_discount');
                return back()
                    ->with('toast_error', 'Mã giảm giá này chưa bắt đầu áp dụng.')
                    ->withInput();
            }

            if ($discount->end_date && $discount->end_date->isPast()) {
                $request->session()->forget('checkout_discount');
                return back()
                    ->with('toast_error', 'Mã giảm giá đã hết hạn.')
                    ->withInput();
            }

            if (
                !is_null($discount->min_order_value_vnd)
                && (int) $discount->min_order_value_vnd > 0
                && $subtotalVnd < (int) $discount->min_order_value_vnd
            ) {
                $request->session()->forget('checkout_discount');
                return back()
                    ->with('toast_error', 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá.')
                    ->withInput();
            }

            if (!is_null($discount->per_user_limit)) {
                $userUsedCount = DiscountUsage::query()
                    ->where('discount_id', $discount->id)
                    ->where('user_id', $user->id)
                    ->whereNotNull('used_at')
                    ->count();

                if ($userUsedCount >= (int) $discount->per_user_limit) {
                    $request->session()->forget('checkout_discount');
                    return back()
                        ->with('toast_error', 'Bạn đã sử dụng mã này tối đa ' . (int) $discount->per_user_limit . ' lần.')
                        ->withInput();
                }
            }

            if (!is_null($discount->usage_limit)) {
                $totalUsed = DiscountUsage::query()
                    ->where('discount_id', $discount->id)
                    ->whereNotNull('used_at')
                    ->count();

                if ($totalUsed >= (int) $discount->usage_limit) {
                    $request->session()->forget('checkout_discount');
                    return back()
                        ->with('toast_error', 'Mã giảm giá đã được sử dụng hết lượt.')
                        ->withInput();
                }
            }

            $discountType = (string) $discount->type;
            $discountId = (string) $discount->id;

            if ($discountType === 'percent') {
                $discountVnd = (int) floor($subtotalVnd * (int) $discount->value / 100);
                if ($discountVnd > $subtotalVnd) {
                    $discountVnd = $subtotalVnd;
                }
            } elseif ($discountType === 'fixed') {
                $discountVnd = (int) $discount->value;
                if ($discountVnd > $subtotalVnd) {
                    $discountVnd = $subtotalVnd;
                }
            } elseif ($discountType === 'shipping') {
                $shippingDiscountVnd = (int) ((int) $discount->value > 0 ? (int) $discount->value : $shippingBaseVnd);
                if ($shippingDiscountVnd > $shippingBaseVnd) {
                    $shippingDiscountVnd = $shippingBaseVnd;
                }
                $discountVnd = 0;
            } else {
                $request->session()->forget('checkout_discount');
                return back()
                    ->with('toast_error', 'Loại mã giảm giá không hợp lệ.')
                    ->withInput();
            }

            if ($discountVnd < 0) {
                $discountVnd = 0;
            }
            if ($shippingDiscountVnd < 0) {
                $shippingDiscountVnd = 0;
            }

            $request->session()->put('checkout_discount', [
                'discount_id'           => $discount->id,
                'code'                  => $discount->code,
                'type'                  => $discount->type,
                'value'                 => $discount->value,
                'discount_vnd'          => $discountVnd,
                'shipping_discount_vnd' => $shippingDiscountVnd,
            ]);
        }

        $shippingFeeVnd = $shippingBaseVnd - $shippingDiscountVnd;
        if ($shippingFeeVnd < 0) {
            $shippingFeeVnd = 0;
        }

        $grandTotalVnd = $subtotalVnd - $discountVnd + $taxVnd + $shippingFeeVnd;
        if ($grandTotalVnd < 0) {
            $grandTotalVnd = 0;
        }

        $orderId = (string) Str::uuid();
        $now = now();

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
            'discount_id'       => $discountId,
            'buyer_note'        => $request->input('buyer_note') ?? null,
            'placed_at'         => $now,
            'delivered_at'      => null,
            'cancelled_at'      => null,
        ];

        $orderItemsData = [];
        $discountRemainingVnd = ($discountType !== 'shipping' && $discountVnd > 0 && $subtotalVnd > 0) ? $discountVnd : 0;
        $lastProductId = (string) end($productIds);

        foreach ($items as $productId => $qty) {
            $product = $products[$productId];

            $quantity = (int) $qty;
            $unitPrice = (int) $product->selling_price_vnd;
            $lineTotal = $quantity * $unitPrice;

            $discountAmountVnd = 0;
            if ($discountRemainingVnd > 0) {
                if ((string) $productId === $lastProductId) {
                    $discountAmountVnd = $discountRemainingVnd;
                } else {
                    $discountAmountVnd = intdiv($lineTotal * $discountVnd, $subtotalVnd);
                    if ($discountAmountVnd > $discountRemainingVnd) {
                        $discountAmountVnd = $discountRemainingVnd;
                    }
                }

                if ($discountAmountVnd > $lineTotal) {
                    $discountAmountVnd = $lineTotal;
                }

                $discountRemainingVnd -= $discountAmountVnd;
                if ($discountRemainingVnd < 0) {
                    $discountRemainingVnd = 0;
                }
            }

            $taxRate = null;
            $taxAmountVnd = 0;
            $unitCostSnapshotVnd = 0;

            $totalPriceVnd = $lineTotal - $discountAmountVnd + $taxAmountVnd;
            if ($totalPriceVnd < 0) {
                $totalPriceVnd = 0;
            }

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
                'created_at'               => $now,
                'updated_at'               => $now,
            ];
        }

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
            'created_at'   => $now,
            'updated_at'   => $now,
        ];

        if ($data['payment_method'] === 'momo') {
            $orderData['payment_method'] = 'momo';
            $orderData['payment_status'] = 'pending';

            $amount = (int) $orderData['grand_total_vnd'];

            $request->session()->put('checkout.momo_pending', [
                'orderData'      => $orderData,
                'orderItemsData' => $orderItemsData,
                'shipmentData'   => $shipmentData,
                'items'          => $items,
            ]);

            $gateway = new MomoGateway(config('payment.momo'));

            try {
                $payUrl = $gateway->createPaymentUrl(
                    $orderData['code'],
                    $amount,
                    'Thanh toán đơn hàng #' . $orderData['code']
                );

                return redirect()->away($payUrl);
            } catch (\Throwable $e) {
                Log::error('MoMo createPaymentUrl failed', [
                    'error' => $e->getMessage(),
                ]);

                $toastMessage = 'Thanh toán MoMo không hợp lệ, vui lòng thử lại hoặc chọn phương thức khác.';

                $raw = $e->getMessage();
                $prefix = 'MoMo API error: ';

                if (str_starts_with($raw, $prefix)) {
                    $json = substr($raw, strlen($prefix));
                    $dataErr = json_decode($json, true);

                    if (is_array($dataErr)) {
                        if (!empty($dataErr['message']) && is_string($dataErr['message'])) {
                            $toastMessage = 'Thanh toán MoMo không hợp lệ: ' . $dataErr['message'];
                        }
                        if (isset($dataErr['resultCode']) && (int) $dataErr['resultCode'] === 22) {
                            $toastMessage = 'Số tiền thanh toán qua MoMo phải từ 10.000đ đến 50.000.000đ. '
                                . 'Vui lòng điều chỉnh giá trị đơn hàng hoặc chọn phương thức thanh toán khác';
                        }
                    }
                }

                return redirect()
                    ->route('checkout.page')
                    ->with('toast_error', $toastMessage)
                    ->withInput();
            }
        }

        if ($data['payment_method'] === 'vnpay') {
            $orderData['payment_method'] = 'vnpay';
            $orderData['payment_status'] = 'pending';

            $request->session()->put('checkout.vnpay_pending', [
                'orderData'      => $orderData,
                'orderItemsData' => $orderItemsData,
                'shipmentData'   => $shipmentData,
                'items'          => $items,
            ]);

            $amount = (int) $orderData['grand_total_vnd'];
            $gateway = new VnpayGateway(config('payment.vnpay'));

            try {
                $payUrl = $gateway->createPaymentUrl(
                    $orderData['code'],
                    $amount,
                    'Thanh toán đơn hàng #' . $orderData['code'],
                    $request->ip()
                );

                return redirect()->away($payUrl);
            } catch (\Throwable $e) {
                Log::error('VNPay createPaymentUrl failed', [
                    'error'  => $e->getMessage(),
                    'code'   => $orderData['code'] ?? null,
                    'amount' => $amount,
                ]);

                $toastMessage = 'Thanh toán VNPAY không hợp lệ, vui lòng thử lại hoặc chọn phương thức khác.';

                $raw = $e->getMessage();
                $prefix = 'VNPay API error: ';

                if (str_starts_with($raw, $prefix)) {
                    $json = substr($raw, strlen($prefix));
                    $dataErr = json_decode($json, true);

                    if (is_array($dataErr)) {
                        if (!empty($dataErr['message']) && is_string($dataErr['message'])) {
                            $toastMessage = 'Thanh toán VNPAY không hợp lệ: ' . $dataErr['message'];
                        }

                        if (!empty($dataErr['code']) && (string) $dataErr['code'] === '24') {
                            $toastMessage = 'Giao dịch VNPAY đã bị hủy. Vui lòng thử lại nếu bạn vẫn muốn thanh toán.';
                        }
                    }
                }

                return redirect()
                    ->route('checkout.page')
                    ->with('toast_error', $toastMessage)
                    ->withInput();
            }
        }

        if ($data['payment_method'] === 'cod') {
            $order = $checkoutService->placeCodOrder(
                $orderData,
                $orderItemsData,
                $shipmentData
            );

            if ($order) {
                if (!empty($discountId)) {
                    $this->recordDiscountUsage((string) $discountId, (string) $user->id, (string) $order->id);
                }

                $this->sendOrderPlacedMail($order);

                $request->session()->forget([
                    'checkout.items',
                    'checkout.expires_at',
                    'checkout_discount',
                ]);

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

        return back()
            ->with('toast_error', 'Phương thức thanh toán không hợp lệ');
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
            $request->session()->forget('checkout_discount');

            return redirect()
                ->route('checkout.page')
                ->with('toast_error', 'Thanh toán MoMo thất bại hoặc bị hủy');
        }

        $orderData      = $pending['orderData'];
        $orderItemsData = $pending['orderItemsData'];
        $shipmentData   = $pending['shipmentData'];
        $items          = $pending['items'];

        $orderData['payment_method'] = 'momo';
        $orderData['payment_status'] = 'paid';

        $order = $checkoutService->placeCodOrder(
            $orderData,
            $orderItemsData,
            $shipmentData
        );

        $request->session()->forget('checkout.momo_pending');
        $request->session()->forget([
            'checkout.items',
            'checkout.expires_at',
            'checkout_discount',
        ]);

        if ($order) {
            $discountId = $orderData['discount_id'] ?? null;
            if (!empty($discountId)) {
                $this->recordDiscountUsage((string) $discountId, (string) $order->user_id, (string) $order->id);
            }

            $this->sendOrderPlacedMail($order);

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

        // ==== VERIFY CHỮ KÝ VNPAY (giữ nguyên code cũ của bạn) ====
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

        // ==== CHECK KẾT QUẢ THANH TOÁN (giữ logic cũ) ====
        $responseCode = $request->input('vnp_ResponseCode');
        if ($responseCode !== '00') {
            $request->session()->forget('checkout.vnpay_pending');
            $request->session()->forget('checkout_discount');

            return redirect()
                ->route('checkout.page')
                ->with('toast_error', 'Thanh toán VNPAY thất bại hoặc đã bị hủy');
        }

        $orderData      = $pending['orderData'];
        $orderItemsData = $pending['orderItemsData'];
        $shipmentData   = $pending['shipmentData'];
        $items          = $pending['items'];

        $orderData['payment_method'] = 'vnpay';
        $orderData['payment_status'] = 'paid';

        $order = $checkoutService->placeCodOrder(
            $orderData,
            $orderItemsData,
            $shipmentData
        );

        $request->session()->forget('checkout.vnpay_pending');
        $request->session()->forget([
            'checkout.items',
            'checkout.expires_at',
            'checkout_discount',
        ]);

        if ($order) {
            $discountId = $orderData['discount_id'] ?? null;
            if (!empty($discountId)) {
                $this->recordDiscountUsage((string) $discountId, (string) $order->user_id, (string) $order->id);
            }

            $this->sendOrderPlacedMail($order);

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

    public function applyDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => ['required', 'string', 'max:64'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'shipping' => ['required', 'integer', 'min:0'],
        ]);

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Bạn cần đăng nhập để sử dụng mã giảm giá.',
            ], 401);
        }

        $code = strtoupper(trim((string) $request->input('code')));
        $subtotal = $request->integer('subtotal');
        $shipping = $request->integer('shipping');

        $discount = Discount::query()
            ->where('code', $code)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$discount) {
            return response()->json([
                'ok'      => false,
                'message' => 'Mã giảm giá không hợp lệ.',
            ], 404);
        }

        if ($discount->start_date && $discount->start_date->isFuture()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Mã giảm giá này chưa bắt đầu áp dụng.',
            ], 422);
        }

        if ($discount->end_date && $discount->end_date->isPast()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Mã giảm giá đã hết hạn.',
            ], 422);
        }

        if (
            !is_null($discount->min_order_value_vnd)
            && $discount->min_order_value_vnd > 0
            && $subtotal < $discount->min_order_value_vnd
        ) {
            return response()->json([
                'ok'      => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá.',
            ], 422);
        }

        // ====== GIỚI HẠN THEO USER (per_user_limit) ======
        if (!is_null($discount->per_user_limit)) {
            $userUsedCount = DiscountUsage::query()
                ->where('discount_id', $discount->id)
                ->where('user_id', $user->id)
                ->whereNotNull('used_at')
                ->count();

            if ($userUsedCount >= $discount->per_user_limit) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Bạn đã sử dụng mã này tối đa ' . $discount->per_user_limit . ' lần.',
                ], 422);
            }
        }

        // ====== GIỚI HẠN TỔNG SỐ LƯỢT (usage_limit) ======
        if (!is_null($discount->usage_limit)) {
            $totalUsed = DiscountUsage::query()
                ->where('discount_id', $discount->id)
                ->whereNotNull('used_at')
                ->count();

            if ($totalUsed >= $discount->usage_limit) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Mã giảm giá đã được sử dụng hết lượt.',
                ], 422);
            }
        }

        $discountAmount = 0;
        $shippingDiscount = 0;

        if ($discount->type === 'percent') {
            $discountAmount = (int) floor($subtotal * $discount->value / 100);
            if ($discountAmount > $subtotal) {
                $discountAmount = $subtotal;
            }
        } elseif ($discount->type === 'fixed') {
            $discountAmount = (int) $discount->value;
            if ($discountAmount > $subtotal) {
                $discountAmount = $subtotal;
            }
        } elseif ($discount->type === 'shipping') {
            $shippingDiscount = $discount->value > 0
                ? (int) $discount->value
                : $shipping;

            if ($shippingDiscount > $shipping) {
                $shippingDiscount = $shipping;
            }
        }

        if ($discountAmount < 0) {
            $discountAmount = 0;
        }

        if ($shippingDiscount < 0) {
            $shippingDiscount = 0;
        }

        $newShipping = $shipping - $shippingDiscount;
        if ($newShipping < 0) {
            $newShipping = 0;
        }

        $total = $subtotal - $discountAmount + $newShipping;
        if ($total < 0) {
            $total = 0;
        }

        $request->session()->put('checkout_discount', [
            'discount_id'           => $discount->id,
            'code'                  => $discount->code,
            'type'                  => $discount->type,
            'value'                 => $discount->value,
            'discount_vnd'          => $discountAmount,
            'shipping_discount_vnd' => $shippingDiscount,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Áp dụng mã giảm giá thành công',
            'data'    => [
                'code'                  => $discount->code,
                'discount_vnd'          => $discountAmount,
                'shipping_discount_vnd' => $shippingDiscount,
                'subtotal_vnd'          => $subtotal,
                'shipping_vnd'          => $newShipping,
                'total_vnd'             => $total,
            ],
        ]);
    }

    protected function recordDiscountUsage(string $discountId, string $userId, string $orderId): void
    {
        if ($discountId === '') {
            return;
        }

        try {
            DiscountUsage::create([
                'discount_id' => $discountId,
                'user_id'     => $userId,
                'order_id'    => $orderId,
                'used_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Cannot record discount usage', [
                'discount_id' => $discountId,
                'user_id'     => $userId,
                'order_id'    => $orderId,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    public function removeDiscount(Request $request): JsonResponse
    {
        $request->session()->forget('checkout_discount');

        return response()->json([
            'ok' => true,
            // 'message' => 'Đã xoá mã giảm giá',
        ]);
    }

    protected function sendOrderPlacedMail(Order $order): void
    {
        $order->loadMissing([
            'items',
            'shipment',
            'user',
        ]);

        $shipment = $order->shipment;
        $user = $order->user;

        $toEmail = null;
        if ($shipment !== null && !empty($shipment->email)) {
            $toEmail = $shipment->email;
        } elseif ($user !== null && !empty($user->email)) {
            $toEmail = $user->email;
        }
        if ($toEmail === null) {
            return;
        }

        try {
            Mail::to($toEmail)->send(new OrderPlacedMail($order));
        } catch (\Throwable $e) {
            Log::error('Send order placed mail failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
