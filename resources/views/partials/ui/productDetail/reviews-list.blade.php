@php
  $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');
@endphp

@if($reviews->count() === 0)
  <div class="reviews-empty text-muted">Chưa có đánh giá nào</div>
@else
  <div class="reviews-list">
    @foreach($reviews as $review)
      @php
        $name = $review->user->name ?? 'Người dùng';
        $avatarChar = mb_substr($name, 0, 1, 'UTF-8');
      @endphp

      <article class="review-card">
        <div class="review-header">
          <div class="review-left">
            <div class="review-avatar">{{ $avatarChar }}</div>
            <div class="review-author">
              <strong class="review-name">{{ $name }}</strong>
              <div class="review-date">
                @if($review->created_at)
                  {{ optional($review->created_at)->timezone($tz)->format('d/m/Y') }}
                @else
                  Không rõ
                @endif
              </div>
            </div>
          </div>
          <div class="review-right" aria-label="{{ (int)$review->rating }} trên 5 sao">
            @for($i = 1; $i <= 5; $i++)
              <i class="fas fa-star {{ $i <= (int)$review->rating ? 'filled' : '' }}"></i>
            @endfor
          </div>
        </div>

        @if($review->comment)
          <div class="review-body">
            {{ $review->comment }}
          </div>
        @else
          <div class="review-body text-muted">
            Người dùng không để lại nội dung đánh giá.
          </div>
        @endif

        @if($review->reply)
          <div class="review-reply">
            <div class="reply-title">Phản hồi từ cửa hàng</div>
            <div class="reply-body">{{ $review->reply }}</div>
          </div>
        @endif
      </article>
    @endforeach
  </div>

  <div class="reviews-pagination mt-3">
    {{ $reviews->links('pagination::bootstrap-5') }}
  </div>
@endif
