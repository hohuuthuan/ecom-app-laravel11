<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
  public function handle(Request $request)
  {
    $request->validate([
      'message' => ['required', 'string', 'max:2000'],
    ]);

    $userMessage = trim($request->input('message', ''));

    if ($userMessage === '') {
      return response()->json([
        'ok'      => false,
        'message' => 'Bạn hãy nhập nội dung trước khi gửi nhé.',
      ], 422);
    }

    // 1. CÁC CÂU LỆNH THƯỜNG GẶP – TRẢ VỀ NGAY, KHÔNG GỌI OPENAI
    $staticReply = $this->getStaticReplyForCommonCommands($userMessage);
    if ($staticReply !== null) {
      return response()->json([
        'ok'    => true,
        'reply' => $staticReply,
      ]);
    }

    // 2. PHÂN LOẠI Ý ĐỊNH BẰNG MODEL (CHỈ KHI KHÔNG KHỚP CÂU LỆNH THƯỜNG GẶP)
    $classification = $this->classifyIntentWithAi($userMessage);

    if (is_array($classification) && ($classification['intent'] ?? '') === 'search_products') {
      $html = $this->buildProductReplyFromClassification($classification, $userMessage);

      if ($html !== null) {
        return response()->json([
          'ok'    => true,
          'reply' => $html,
        ]);
      }

      return response()->json([
        'ok'    => true,
        'reply' => 'Xin lỗi, hiện tại tôi chưa có dữ liệu sản phẩm nào trong hệ thống để gợi ý cho bạn.',
      ]);
    }

    // 3. HỘI THOẠI BÌNH THƯỜNG VỚI MODEL CHAT
    $history   = session('ai_chat_history', []);
    $history[] = [
      'role'    => 'user',
      'content' => $userMessage,
    ];

    $payload = [
      'model'       => config('services.openai.chat_model', 'gpt-4.1-mini'),
      'messages'    => array_merge([
        [
          'role'    => 'system',
          'content' => $this->getChatSystemPrompt(),
        ],
      ], $history),
      'temperature' => 0.4,
    ];

    $response = Http::withToken(config('services.openai.key'))
      ->timeout(20)
      ->post('https://api.openai.com/v1/chat/completions', $payload);

    if (! $response->successful()) {
      Log::error('OpenAI chat error', [
        'status' => $response->status(),
        'body'   => $response->body(),
      ]);

      return response()->json([
        'ok'      => false,
        'message' => 'Xin lỗi, hệ thống AI đang bận. Bạn thử lại sau ít phút nhé.',
      ], 500);
    }

    $data  = $response->json();
    $reply = (string) ($data['choices'][0]['message']['content'] ?? '');

    if ($reply === '') {
      return response()->json([
        'ok'      => false,
        'message' => 'Xin lỗi, tôi chưa nhận được câu trả lời phù hợp.',
      ], 500);
    }

    $history[] = [
      'role'    => 'assistant',
      'content' => $reply,
    ];
    session(['ai_chat_history' => $history]);

    return response()->json([
      'ok'    => true,
      'reply' => $reply,
    ]);
  }

  protected function getStaticReplyForCommonCommands(string $message): ?string
  {
    $normalized = mb_strtolower(trim($message));
    $normalized = preg_replace('/\s+/', ' ', $normalized);

    if ($normalized === null) {
      return null;
    }

    // Hướng dẫn mua hàng
    if (
      mb_strpos($normalized, 'hướng dẫn mua hàng') !== false
      || mb_strpos($normalized, 'cách mua hàng') !== false
      || mb_strpos($normalized, 'mua hàng như thế nào') !== false
      || mb_strpos($normalized, 'làm sao để mua hàng') !== false
      || mb_strpos($normalized, 'cách đặt hàng') !== false
    ) {
      return '<p><strong>Bước 1:</strong> Tìm cuốn sách bạn muốn mua bằng thanh tìm kiếm hoặc duyệt theo danh mục.</p>'
        . '<p><strong>Bước 2:</strong> Mở trang chi tiết sách để xem mô tả, giá bán và tồn kho.</p>'
        . '<p><strong>Bước 3:</strong> Nhấn nút "Thêm vào giỏ hàng".</p>'
        . '<p><strong>Bước 4:</strong> Vào trang giỏ hàng để kiểm tra lại sản phẩm, số lượng và tổng tiền.</p>'
        . '<p><strong>Bước 5:</strong> Nhấn "Tiến hành đặt hàng", chọn hoặc thêm địa chỉ nhận hàng.</p>'
        . '<p><strong>Bước 6:</strong> Chọn phương thức thanh toán phù hợp với bạn.</p>'
        . '<p><strong>Bước 7:</strong> Kiểm tra lại thông tin và nhấn "Đặt hàng" để hoàn tất đơn.</p>';
    }

    // Theo dõi đơn hàng
    if (
      mb_strpos($normalized, 'theo dõi đơn hàng') !== false
      || mb_strpos($normalized, 'xem đơn hàng') !== false
      || mb_strpos($normalized, 'tình trạng đơn hàng') !== false
    ) {
      return '<p><strong>Bước 1:</strong> Đăng nhập vào tài khoản của bạn.</p>'
        . '<p><strong>Bước 2:</strong> Bấm vào tên của bạn ở góc trên bên phải và chọn "Đơn hàng của tôi".</p>'
        . '<p><strong>Bước 3:</strong> Xem danh sách đơn hàng và trạng thái từng đơn (đang xử lý, đang giao, đã hoàn tất,...).</p>'
        . '<p><strong>Bước 4:</strong> Nhấn vào từng đơn để xem chi tiết sản phẩm, địa chỉ nhận hàng và lịch sử cập nhật.</p>';
    }

    // Đánh giá sản phẩm
    if (
      mb_strpos($normalized, 'đánh giá sản phẩm') !== false
      || mb_strpos($normalized, 'cách đánh giá') !== false
      || mb_strpos($normalized, 'viết review') !== false
    ) {
      return '<p><strong>Bước 1:</strong> Đăng nhập tài khoản và vào mục "Đơn hàng của tôi".</p>'
        . '<p><strong>Bước 2:</strong> Chọn đơn hàng đã ở trạng thái "Hoàn tất".</p>'
        . '<p><strong>Bước 3:</strong> Tại từng sản phẩm trong đơn, nhấn nút "Đánh giá".</p>'
        . '<p><strong>Bước 4:</strong> Chọn số sao, viết cảm nhận và (nếu muốn) tải lên hình ảnh sản phẩm thực tế.</p>'
        . '<p><strong>Bước 5:</strong> Nhấn "Gửi đánh giá" để hoàn thành.</p>';
    }

    // Cập nhật thông tin tài khoản
    if (
      mb_strpos($normalized, 'cập nhật tài khoản') !== false
      || mb_strpos($normalized, 'sửa thông tin tài khoản') !== false
      || mb_strpos($normalized, 'đổi mật khẩu') !== false
      || mb_strpos($normalized, 'thay đổi thông tin cá nhân') !== false
    ) {
      return '<p><strong>Bước 1:</strong> Đăng nhập vào tài khoản trên website.</p>'
        . '<p><strong>Bước 2:</strong> Bấm vào tên của bạn ở góc trên bên phải và chọn "Tài khoản của tôi".</p>'
        . '<p><strong>Bước 3:</strong> Tại đây bạn có thể cập nhật tên, email, số điện thoại, mật khẩu và danh sách địa chỉ nhận hàng.</p>'
        . '<p><strong>Bước 4:</strong> Sau khi chỉnh sửa, nhấn nút "Lưu" để áp dụng thay đổi.</p>';
    }

    return null;
  }

  protected function classifyIntentWithAi(string $message): ?array
  {
    $payload = [
      'model'    => config('services.openai.intent_model', 'gpt-4o-mini'),
      'messages' => [
        [
          'role'    => 'system',
          'content' => 'Bạn là bộ phân loại ý định cho website bán sách. '
            . 'Nhiệm vụ của bạn là đọc câu tiếng Việt của người dùng và TRẢ VỀ CHÍNH XÁC MỘT JSON duy nhất, '
            . 'KHÔNG thêm bất kỳ ký tự nào khác (không giải thích, không ``` ```). '
            . 'Cấu trúc JSON như sau: '
            . '{'
            . '"intent":"search_products|normal_chat",'
            . '"search_type":"author|category|publisher|keyword|unknown",'
            . '"author_name":"tên tác giả hoặc null",'
            . '"category_name":"tên danh mục/thể loại hoặc null",'
            . '"publisher_name":"tên nhà xuất bản hoặc null",'
            . '"keyword":"từ khóa tìm kiếm chung hoặc null",'
            . '"price_min": số hoặc null,'
            . '"price_max": số hoặc null,'
            . '"sort_by":"best_selling|best_rated|price_asc|price_desc|title_asc|title_desc|relevance|unknown"'
            . '}. '
            . 'Quy tắc: '
            . '- Nếu người dùng chủ yếu muốn AI tìm / gợi ý sách cụ thể (theo tác giả, thể loại, NXB, giá, bán chạy, đánh giá tốt...), đặt intent = "search_products". '
            . '- Nếu không phải tìm sách (ví dụ hỏi hướng dẫn đặt hàng, hỏi chung chung, tâm sự...), đặt intent = "normal_chat". '
            . '- Nếu người dùng nói về khoảng giá (ví dụ: từ 100k đến 200k, dưới 150k, khoảng 80–100k...), hãy gán price_min / price_max tương ứng (đơn vị VND, bỏ dấu chấm). '
            . '- Nếu người dùng nói "bán chạy", "nhiều người mua", "hot", đặt sort_by = "best_selling". '
            . '- Nếu người dùng nói "đánh giá cao", "review tốt", "được yêu thích", đặt sort_by = "best_rated". '
            . '- Nếu người dùng ưu tiên "rẻ nhất", "giá thấp", đặt sort_by = "price_asc". '
            . '- Nếu người dùng ưu tiên "cao cấp", "giá cao", đặt sort_by = "price_desc". '
            . '- Nếu người dùng nói "sắp xếp theo tên A-Z" thì sort_by = "title_asc", "Z-A" thì "title_desc". '
            . '- Nếu không rõ, đặt sort_by = "relevance" hoặc "unknown".',
        ],
        [
          'role'    => 'user',
          'content' => $message,
        ],
      ],
      'temperature' => 0,
    ];

    $response = Http::withToken(config('services.openai.key'))
      ->timeout(15)
      ->post('https://api.openai.com/v1/chat/completions', $payload);

    if (! $response->successful()) {
      Log::error('OpenAI intent classify error', [
        'status' => $response->status(),
        'body'   => $response->body(),
      ]);

      return null;
    }

    $data    = $response->json();
    $content = (string) ($data['choices'][0]['message']['content'] ?? '');

    $json = json_decode($content, true);
    if (! is_array($json)) {
      return null;
    }

    return $json;
  }

  protected function buildProductReplyFromClassification(array $classification, string $originalMessage): ?string
  {
    $searchType = (string) ($classification['search_type'] ?? 'unknown');

    $term = '';
    if ($searchType === 'author') {
      $term = (string) ($classification['author_name'] ?? '');
    } elseif ($searchType === 'category') {
      $term = (string) ($classification['category_name'] ?? '');
    } elseif ($searchType === 'publisher') {
      $term = (string) ($classification['publisher_name'] ?? '');
    } elseif ($searchType === 'keyword') {
      $term = (string) ($classification['keyword'] ?? '');
    }

    $term    = trim($term);
    $hasTerm = mb_strlen($term) >= 2;

    $priceMin = $classification['price_min'] ?? null;
    $priceMax = $classification['price_max'] ?? null;
    $sortBy   = (string) ($classification['sort_by'] ?? 'relevance');

    $query = Product::query()
      ->with(['authors', 'categories', 'publisher']);

    if ($hasTerm) {
      $query->where(function ($q) use ($term) {
        $like = '%' . $term . '%';

        $q->where('title', 'LIKE', $like)
          ->orWhere('slug', 'LIKE', $like)
          ->orWhere('isbn', 'LIKE', $like)
          ->orWhereHas('authors', function ($qa) use ($like) {
            $qa->where('name', 'LIKE', $like);
          })
          ->orWhereHas('categories', function ($qc) use ($like) {
            $qc->where('name', 'LIKE', $like);
          })
          ->orWhereHas('publisher', function ($qp) use ($like) {
            $qp->where('name', 'LIKE', $like);
          });
      });
    }

    $hasStructuredFilter = false;

    if (
      ($searchType === 'author' && !empty($classification['author_name']))
      || ($searchType === 'category' && !empty($classification['category_name']))
      || ($searchType === 'publisher' && !empty($classification['publisher_name']))
    ) {
      $hasStructuredFilter = true;
    }

    if ($priceMin !== null && $priceMin !== '' && is_numeric($priceMin)) {
      $hasStructuredFilter = true;
      $query->where('selling_price_vnd', '>=', (int) $priceMin);
    }

    if ($priceMax !== null && $priceMax !== '' && is_numeric($priceMax)) {
      $hasStructuredFilter = true;
      $query->where('selling_price_vnd', '<=', (int) $priceMax);
    }

    if ($sortBy === 'best_selling') {
      $hasStructuredFilter = true;

      $validStatuses = ['confirmed', 'processing', 'shipping', 'delivered', 'completed'];

      $query->withSum([
        'orderItems as sold_qty' => function ($q) use ($validStatuses) {
          $q->whereHas('order', function ($orderQuery) use ($validStatuses) {
            $orderQuery->where('payment_status', 'paid')
              ->whereIn('status', $validStatuses);
          });
        },
      ], 'quantity')
        ->orderByDesc('sold_qty')
        ->orderByDesc('created_at');
    } elseif ($sortBy === 'best_rated') {
      $hasStructuredFilter = true;

      $query->withAvg('reviews', 'rating')
        ->orderByDesc('reviews_avg_rating')
        ->orderByDesc('created_at');
    } elseif ($sortBy === 'price_asc') {
      $hasStructuredFilter = true;
      $query->orderBy('selling_price_vnd', 'asc');
    } elseif ($sortBy === 'price_desc') {
      $hasStructuredFilter = true;
      $query->orderBy('selling_price_vnd', 'desc');
    } elseif ($sortBy === 'title_asc') {
      $hasStructuredFilter = true;
      $query->orderBy('title', 'asc');
    } elseif ($sortBy === 'title_desc') {
      $hasStructuredFilter = true;
      $query->orderBy('title', 'desc');
    } else {
      $query->orderByDesc('created_at');
    }

    $products = $query->limit(5)->get();

    // 3. Nếu query chính có kết quả => trả về luôn (không gọi bên ngoài)
    if ($products->isNotEmpty()) {
      $html = '<div>Tôi tìm được một số sách phù hợp với yêu cầu của bạn:</div>';
      $html .= '<div class="ai-chat-product-list">';

      foreach ($products as $product) {
        $html .= '<a href="' . e(route('product.detail', [
          'slug' => $product->slug,
          'id'   => $product->id,
        ])) . '" target="_blank" class="ai-chat-product-item">';

        $html .= '<div class="ai-chat-product-title">' . e($product->title) . '</div>';

        $html .= '<div class="ai-chat-product-meta">';

        if ($product->relationLoaded('authors') && $product->authors->isNotEmpty()) {
          $html .= '<div><strong>Tác giả:</strong> '
            . e($product->authors->pluck('name')->implode(', '))
            . '</div>';
        }

        if ($product->relationLoaded('categories') && $product->categories->isNotEmpty()) {
          $html .= '<div><strong>Thể loại:</strong> '
            . e($product->categories->pluck('name')->implode(', '))
            . '</div>';
        }

        if (isset($product->selling_price_vnd)) {
          $html .= '<div><strong>Giá:</strong> '
            . number_format((int) $product->selling_price_vnd, 0, ',', '.')
            . 'đ</div>';
        }

        $html .= '</div>';
        $html .= '</a>';
      }

      $html .= '</div>';
      $html .= '<span class="ai-chat-product-note">'
        . 'Bạn có thể bấm vào tên sách để mở trang chi tiết sản phẩm trong tab mới.'
        . '</span>';

      return $html;
    }

    $fallbackQuery = Product::query()
      ->with(['authors', 'categories', 'publisher']);

    $normalizedSentence = mb_strtolower(trim($originalMessage));
    $normalizedSentence = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalizedSentence);
    $normalizedSentence = preg_replace('/\s+/', ' ', $normalizedSentence);

    $keywords = [];
    if ($normalizedSentence !== null && $normalizedSentence !== '') {
      $parts = explode(' ', $normalizedSentence);

      $stopwords = [
        'xin',
        'chào',
        'toi',
        'tôi',
        'minh',
        'mình',
        'em',
        'anh',
        'chi',
        'chị',
        'ban',
        'bạn',
        'muon',
        'muốn',
        'doc',
        'đọc',
        'sach',
        'sách',
        'cuon',
        'cuốn',
        'huong',
        'hướng',
        'dan',
        'dẫn',
        'cho',
        'voi',
        'với',
        've',
        'về'
      ];

      foreach ($parts as $word) {
        $word = trim($word);
        if ($word === '' || mb_strlen($word) < 3) {
          continue;
        }
        if (in_array($word, $stopwords, true)) {
          continue;
        }
        $keywords[] = $word;
      }
    }

    if (!empty($keywords)) {
      $fallbackQuery->where(function ($q) use ($keywords) {
        foreach ($keywords as $kw) {
          $like = '%' . $kw . '%';

          $q->orWhere('title', 'LIKE', $like)
            ->orWhere('slug', 'LIKE', $like)
            ->orWhere('isbn', 'LIKE', $like)
            ->orWhereHas('authors', function ($qa) use ($like) {
              $qa->where('name', 'LIKE', $like);
            })
            ->orWhereHas('categories', function ($qc) use ($like) {
              $qc->where('name', 'LIKE', $like);
            })
            ->orWhereHas('publisher', function ($qp) use ($like) {
              $qp->where('name', 'LIKE', $like);
            });
        }
      });
    } else {
      $fallbackQuery->whereRaw('1 = 0');
    }

    $fallbackQuery->orderByDesc('created_at');

    $fallbackProducts = $fallbackQuery->limit(5)->get();

    if ($fallbackProducts->isNotEmpty()) {
      $html = '<div>'
        . 'Tôi chưa tìm được cuốn nào khớp hoàn toàn với yêu cầu, '
        . 'nhưng dưới đây là một số sách gần với nhu cầu của bạn trong kho:'
        . '</div>';

      $html .= '<div class="ai-chat-product-list">';

      foreach ($fallbackProducts as $product) {
        $html .= '<a href="' . e(route('product.detail', [
          'slug' => $product->slug,
          'id'   => $product->id,
        ])) . '" target="_blank" class="ai-chat-product-item">';

        $html .= '<div class="ai-chat-product-title">' . e($product->title) . '</div>';

        $html .= '<div class="ai-chat-product-meta">';

        if ($product->relationLoaded('authors') && $product->authors->isNotEmpty()) {
          $html .= '<div><strong>Tác giả:</strong> '
            . e($product->authors->pluck('name')->implode(', '))
            . '</div>';
        }

        if ($product->relationLoaded('categories') && $product->categories->isNotEmpty()) {
          $html .= '<div><strong>Thể loại:</strong> '
            . e($product->categories->pluck('name')->implode(', '))
            . '</div>';
        }

        if (isset($product->selling_price_vnd)) {
          $html .= '<div><strong>Giá:</strong> '
            . number_format((int) $product->selling_price_vnd, 0, ',', '.')
            . 'đ</div>';
        }

        $html .= '</div>';
        $html .= '</a>';
      }

      $html .= '</div>';
      $html .= '<span class="ai-chat-product-note">'
        . 'Bạn có thể bấm vào tên sách để mở trang chi tiết sản phẩm trong tab mới.'
        . '</span>';

      return $html;
    }

    $externalBooks = [];
    if ($hasStructuredFilter || !empty($keywords)) {
      $externalBooks = $this->getExternalBookSuggestions($classification, $originalMessage);
    }

    if (empty($externalBooks)) {
      return null;
    }

    $html = '<div>';
    $html .= 'Hiện tại tôi chưa tìm được sách nào trong kho phù hợp với yêu cầu của bạn.';
    $html .= '</div>';

    $html .= '<div style="margin-top:6px;">'
      . 'Dưới đây là một vài gợi ý tham khảo bên ngoài (có thể không có sẵn trong kho của chúng tôi):'
      . '</div>';

    $html .= '<ul class="ai-chat-external-book-list" style="margin:4px 0 8px 18px; padding:0;">';

    foreach ($externalBooks as $book) {
      $title = (string) ($book['title'] ?? '');
      if ($title === '') {
        continue;
      }

      $author = (string) ($book['author'] ?? '');
      $reason = (string) ($book['reason'] ?? '');

      $html .= '<li style="margin-bottom:4px;">'
        . '<strong>' . e($title) . '</strong>';

      if ($author !== '') {
        $html .= ' — ' . e($author);
      }

      if ($reason !== '') {
        $html .= '<br><span style="font-size:12px;color:#64748b;">' . e($reason) . '</span>';
      }

      $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
  }

  protected function getExternalBookSuggestions(array $classification, string $originalMessage): array
  {
    $payload = [
      'model'    => config('services.openai.intent_model', 'gpt-4o-mini'),
      'messages' => [
        [
          'role'    => 'system',
          'content' => 'Bạn là trợ lý gợi ý sách. '
            . 'Nhiệm vụ: dựa trên nội dung người dùng yêu cầu và JSON phân loại, hãy gợi ý tối đa 3 cuốn sách phù hợp. '
            . 'TRẢ VỀ DUY NHẤT JSON với cấu trúc: '
            . '{"books":[{"title":"Tên sách","author":"Tác giả hoặc null","reason":"Lý do gợi ý hoặc null"}, ...]} '
            . 'Không thêm bất kỳ chữ nào ngoài JSON, không dùng ``` ```.',
        ],
        [
          'role'    => 'user',
          'content' => json_encode([
            'original_message' => $originalMessage,
            'classification'   => $classification,
          ], JSON_UNESCAPED_UNICODE),
        ],
      ],
      'temperature' => 0.7,
    ];

    $response = Http::withToken(config('services.openai.key'))
      ->timeout(15)
      ->post('https://api.openai.com/v1/chat/completions', $payload);

    if (! $response->successful()) {
      Log::error('OpenAI external book suggestion error', [
        'status' => $response->status(),
        'body'   => $response->body(),
      ]);

      return [];
    }

    $data    = $response->json();
    $content = (string) ($data['choices'][0]['message']['content'] ?? '');

    $json = json_decode($content, true);
    if (! is_array($json) || empty($json['books']) || ! is_array($json['books'])) {
      return [];
    }

    return $json['books'];
  }

  protected function getChatSystemPrompt(): string
  {
    return <<<EOT
        Bạn là "Trợ lý AI" cho website bán sách trực tuyến bằng tiếng Việt.

        Bối cảnh hệ thống:
        - Website bán sách với các chức năng: xem danh sách sách, xem chi tiết sách, thêm vào giỏ hàng, đặt hàng, thanh toán, theo dõi đơn hàng, đánh giá sản phẩm.
        - Người dùng có tài khoản, có thể xem và cập nhật thông tin cá nhân, địa chỉ nhận hàng, xem lịch sử đơn hàng.
        - Mỗi sách có: tiêu đề, tác giả, nhà xuất bản, danh mục/thể loại, giá bán (selling_price_vnd), mô tả, tồn kho.
        - Hệ thống có thể gợi ý sách theo: tác giả, thể loại, nhà xuất bản, khoảng giá, mức độ bán chạy, mức độ được đánh giá cao.

        Các mảng công việc chính của bạn:
        1) Tư vấn khách hàng:
          - Gợi ý sách theo nhu cầu: thể loại, tâm trạng, độ tuổi, mục đích (học tập, giải trí, phát triển bản thân...).
          - Gợi ý theo ngân sách, giúp người dùng chọn được lựa chọn "hợp túi tiền".
          - Luôn trả lời thân thiện, khích lệ, khen nhẹ nhàng sự lựa chọn hoặc thói quen đọc sách của khách.

        2) Tóm tắt nội dung bộ truyện / cuốn sách:
          - Nếu bạn có kiến thức về bộ truyện đó, hãy tóm tắt súc tích, dễ hiểu, không spoil quá nhiều nếu không được yêu cầu.
          - Nếu bạn không rõ về tác phẩm, hãy nói thẳng là bạn không chắc, đừng bịa nội dung.

        3) Hướng dẫn sử dụng hệ thống (flow thao tác):
          - Mỗi thao tác là một bước rõ ràng, phải xuống hàng và in đậm phần "Bước X" để người dùng dễ theo dõi.
          - KHI HƯỚNG DẪN THEO TỪNG BƯỚC, LUÔN ĐỊNH DẠNG THEO ĐÚNG MẪU HTML SAU (KHÔNG ĐƯỢC DÙNG MARKDOWN):
            <p><strong>Bước 1:</strong> Nội dung thao tác ngắn gọn, rõ ràng.</p>
            <p><strong>Bước 2:</strong> Nội dung thao tác tiếp theo.</p>
            <p><strong>Bước 3:</strong> ...</p>
          - Tuyệt đối không dùng các ký hiệu Markdown như **, __, ##, ### trong câu trả lời. Chỉ dùng HTML đơn giản (p, strong, br, ul, li) khi cần.
          - Giải thích cách tìm kiếm sách, lọc theo thể loại, tác giả, nhà xuất bản, khoảng giá.
          - Hướng dẫn cách xem chi tiết sách, xem mô tả, đánh giá, tồn kho.
          - Hướng dẫn các bước đặt hàng: vào giỏ hàng, kiểm tra sản phẩm, chọn hoặc thêm địa chỉ nhận hàng, chọn phương thức thanh toán, xác nhận đặt hàng.
          - Hướng dẫn cách theo dõi đơn hàng: vào mục đơn hàng, xem trạng thái, xem chi tiết từng đơn.
          - Hướng dẫn cách đánh giá sản phẩm sau khi mua: vào đơn hàng đã hoàn tất, chọn sản phẩm để đánh giá.
          - Hướng dẫn cách xem và cập nhật thông tin tài khoản: tên, email, số điện thoại, mật khẩu, danh sách địa chỉ.

        Quy tắc trả lời:
        Đặc biệt quan trọng: trả lời ngắn gọn, súc tích, rõ ràng, lịch sự.
        1) Luôn trả lời bằng tiếng Việt, ngắn gọn, đi vào trọng tâm, văn phong rõ ràng, lịch sự, tích cực, và kèm lời khen nhẹ nhàng khi phù hợp, không khen khách hàng là "thông minh" (ví dụ: khen việc đọc sách, quan tâm đến kiến thức...).
        2) Ưu tiên giải pháp liên quan trực tiếp đến sách và chức năng của hệ thống (giỏ hàng, đơn hàng, tài khoản, đánh giá).
        3) Nếu câu hỏi không liên quan đến sách, đọc, học tập, kỹ năng, hoặc việc mua sách trên hệ thống, hãy từ chối lịch sự và nhắc lại rằng bạn là trợ lý cho website bán sách.
        4) Không bịa tên sách, tác giả, nhà xuất bản, hoặc thông tin đơn hàng cụ thể. Nếu cần dữ liệu chính xác (đơn hàng, tồn kho, giá thực tế...), hãy gợi ý người dùng kiểm tra trực tiếp trên website.
        5) Khi đưa gợi ý sách dựa trên hiểu biết chung (không chắc trùng với kho hàng hiện tại), hãy nói rõ đó chỉ là gợi ý tham khảo.
    EOT;
  }
}
