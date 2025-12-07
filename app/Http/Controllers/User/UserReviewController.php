<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Throwable;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class UserReviewController extends Controller
{
    public function showOrderReview(string $id): View
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $order = Order::with(['items.product'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Lấy danh sách product_id trong đơn
        $productIds = $order->items
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Lấy các review của user cho đơn này, key theo product_id
        $reviewsByProduct = [];
        if (!empty($productIds)) {
            $reviewsByProduct = Review::query()
                ->where('user_id', $userId)
                ->where('order_id', $order->id)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');
        }

        return view('user.orderReview', [
            'order'            => $order,
            'reviewsByProduct' => $reviewsByProduct,
        ]);
    }

    public function storeFromOrder(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để gửi đánh giá.',
                ], 401);
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'order_id'      => ['required', 'uuid', 'exists:orders,id'],
                    'product_id'    => ['required', 'uuid', 'exists:products,id'],
                    'order_item_id' => ['nullable'],

                    'rating'        => ['required', 'integer', 'min:1', 'max:5'],
                    'comment'       => ['required', 'string', 'max:2000'],
                    'image'         => ['required', 'image', 'max:2048'],
                ],
                [
                    'order_id.required'   => 'Thiếu thông tin đơn hàng.',
                    'order_id.uuid'       => 'Đơn hàng không hợp lệ.',
                    'order_id.exists'     => 'Không tìm thấy đơn hàng.',

                    'product_id.required' => 'Thiếu thông tin sản phẩm.',
                    'product_id.uuid'     => 'Sản phẩm không hợp lệ.',
                    'product_id.exists'   => 'Không tìm thấy sản phẩm.',

                    'rating.required'     => 'Vui lòng chọn số sao.',
                    'rating.integer'      => 'Số sao không hợp lệ.',
                    'rating.min'          => 'Số sao tối thiểu là 1.',
                    'rating.max'          => 'Số sao tối đa là 5.',

                    'comment.required'    => 'Vui lòng nhập nội dung đánh giá.',
                    'comment.string'      => 'Nội dung đánh giá không hợp lệ.',
                    'comment.max'         => 'Nội dung đánh giá tối đa 2000 ký tự.',

                    'image.required'      => 'Vui lòng chọn hình minh hoạ.',
                    'image.image'         => 'File tải lên phải là hình ảnh.',
                    'image.max'           => 'Dung lượng ảnh tối đa 2MB.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            $order = Order::where('id', $validated['order_id'])
                ->where('user_id', $user->id)
                ->first();

            if ($order === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng không hợp lệ.',
                ], 404);
            }

            $product = Product::find($validated['product_id']);
            if ($product === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại.',
                ], 404);
            }

            $exists = Review::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng này rồi.',
                ], 422);
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('reviews', 'public');
            }

            $review = Review::create([
                'id'         => (string) \Illuminate\Support\Str::uuid(),
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'user_id'    => $user->id,
                'rating'     => $validated['rating'],
                'comment'    => $validated['comment'],
                'image'      => $imagePath,
                'is_active'  => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gửi đánh giá thành công.',
                'data'    => [
                    'id'         => $review->id,
                    'rating'     => $review->rating,
                    'comment'    => $review->comment,
                    'image_url'  => $imagePath ? asset('storage/' . $imagePath) : null,
                    'product_id' => $product->id,
                    'order_id'   => $order->id,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Store review from order failed', [
                'message'    => $e->getMessage(),
                'user_id'    => Auth::id(),
                'order_id'   => $request->input('order_id'),
                'product_id' => $request->input('product_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.',
            ], 500);
        }
    }
}
