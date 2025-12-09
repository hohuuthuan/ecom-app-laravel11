<div
  id="aiChatWidget"
  class="ai-chat-widget"
  data-chat-url="{{ route('chat.ai') }}">
  <button
    id="aiChatToggle"
    type="button"
    class="ai-chat-toggle"
    aria-label="Mở chat AI">
    <img
      src="{{ asset('storage/AI/avatar-AI.avif') }}"
      alt="Chat AI"
      class="ai-chat-toggle-icon">
  </button>

  <div id="aiChatWindow" class="ai-chat-window ai-hidden">
    <header class="ai-chat-header">
      <div class="ai-chat-header-left">
        <div class="ai-chat-avatar">
          <span class="ai-chat-avatar-icon">🤖</span>
        </div>
        <div class="ai-chat-header-text">
          <h2 class="ai-chat-title">Trợ lý AI</h2>
          <p class="ai-chat-status text-success">Đang hoạt động</p>
        </div>
      </div>

      <div class="ai-chat-header-actions">
        <button
          type="button"
          id="aiChatClearHistory"
          class="ai-chat-clear-history"
          aria-label="Xóa lịch sử chat">
          Xóa lịch sử chat
        </button>

        <button
          type="button"
          id="aiChatClose"
          class="ai-chat-close"
          aria-label="Đóng chat">
          ×
        </button>
      </div>
    </header>
    <main id="aiChatMessages" class="ai-chat-messages">
      <div class="ai-chat-message-row ai-chat-message-row-assistant message-enter">
        <div class="ai-chat-bubble ai-chat-bubble-assistant">
          <p class="ai-chat-bubble-text">
            Xin chào! Tôi có thể giúp gì cho bạn hôm nay?
          </p>
        </div>
      </div>
    </main>

    <footer class="ai-chat-footer">
      <form id="aiChatForm" class="ai-chat-form" autocomplete="off">
        <input
          type="text"
          id="aiChatInput"
          class="ai-chat-input"
          placeholder="Nhập tin nhắn của bạn..."
          aria-label="Nhập tin nhắn của bạn">
        <button
          type="submit"
          id="aiChatSend"
          class="ai-chat-send"
          aria-label="Gửi tin nhắn">
          Gửi
        </button>
      </form>
    </footer>
  </div>
</div>