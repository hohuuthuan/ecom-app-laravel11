(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var widget = document.getElementById('aiChatWidget');
    if (!widget) {
      return;
    }

    var chatUrl = widget.getAttribute('data-chat-url');
    var toggle = document.getElementById('aiChatToggle');
    var windowEl = document.getElementById('aiChatWindow');
    var closeBtn = document.getElementById('aiChatClose');
    var clearBtn = document.getElementById('aiChatClearHistory');
    var form = document.getElementById('aiChatForm');
    var input = document.getElementById('aiChatInput');
    var messagesEl = document.getElementById('aiChatMessages');
    var sendBtn = document.getElementById('aiChatSend');

    if (!chatUrl || !toggle || !windowEl || !closeBtn || !clearBtn || !form || !input || !messagesEl || !sendBtn) {
      return;
    }

    var typingEl = null;
    var faqButtons = widget.querySelectorAll('[data-faq-key]');
    var STORAGE_KEY = 'ai_chat_history_v1';

    var FAQ_CONFIG = {
      buy_flow: {
        question: 'Cách mua hàng trên website',
        answer:
          '<p><strong>Bước 1:</strong> Tìm cuốn sách bạn muốn mua bằng thanh tìm kiếm hoặc duyệt theo danh mục.</p>' +
          '<p><strong>Bước 2:</strong> Mở trang chi tiết sách để xem mô tả, giá bán và tồn kho.</p>' +
          '<p><strong>Bước 3:</strong> Nhấn nút "Thêm vào giỏ hàng".</p>' +
          '<p><strong>Bước 4:</strong> Vào trang giỏ hàng để kiểm tra lại sản phẩm, số lượng và tổng tiền.</p>' +
          '<p><strong>Bước 5:</strong> Nhấn "Tiến hành đặt hàng", chọn hoặc thêm địa chỉ nhận hàng.</p>' +
          '<p><strong>Bước 6:</strong> Chọn phương thức thanh toán phù hợp với bạn.</p>' +
          '<p><strong>Bước 7:</strong> Kiểm tra lại thông tin và nhấn "Đặt hàng" để hoàn tất đơn.</p>'
      },
      track_order: {
        question: 'Cách theo dõi tình trạng đơn hàng',
        answer:
          '<p><strong>Bước 1:</strong> Đăng nhập vào tài khoản của bạn.</p>' +
          '<p><strong>Bước 2:</strong> Bấm vào tên của bạn ở góc trên bên phải và chọn "Đơn hàng của tôi".</p>' +
          '<p><strong>Bước 3:</strong> Xem danh sách đơn hàng và trạng thái từng đơn (đang xử lý, đang giao, đã hoàn tất,...).</p>' +
          '<p><strong>Bước 4:</strong> Nhấn vào từng đơn để xem chi tiết sản phẩm, địa chỉ nhận hàng và lịch sử cập nhật.</p>'
      },
      review_product: {
        question: 'Cách đánh giá sản phẩm sau khi mua',
        answer:
          '<p><strong>Bước 1:</strong> Đăng nhập tài khoản và vào mục "Đơn hàng của tôi".</p>' +
          '<p><strong>Bước 2:</strong> Chọn đơn hàng đã ở trạng thái "Hoàn tất".</p>' +
          '<p><strong>Bước 3:</strong> Tại từng sản phẩm trong đơn, nhấn nút "Đánh giá".</p>' +
          '<p><strong>Bước 4:</strong> Chọn số sao, viết cảm nhận và (nếu muốn) tải lên hình ảnh sản phẩm thực tế.</p>' +
          '<p><strong>Bước 5:</strong> Nhấn "Gửi đánh giá" để hoàn thành.</p>'
      },
      update_account: {
        question: 'Cách cập nhật thông tin tài khoản',
        answer:
          '<p><strong>Bước 1:</strong> Đăng nhập vào tài khoản trên website.</p>' +
          '<p><strong>Bước 2:</strong> Bấm vào tên của bạn ở góc trên bên phải và chọn "Tài khoản của tôi".</p>' +
          '<p><strong>Bước 3:</strong> Tại đây bạn có thể cập nhật tên, email, số điện thoại, mật khẩu và danh sách địa chỉ nhận hàng.</p>' +
          '<p><strong>Bước 4:</strong> Sau khi chỉnh sửa, nhấn nút "Lưu" để áp dụng thay đổi.</p>'
      }
    };

    function clearMessages() {
      while (messagesEl.firstChild) {
        messagesEl.removeChild(messagesEl.firstChild);
      }
    }

    function isNearBottom() {
      var threshold = 80;
      var distance = messagesEl.scrollHeight - (messagesEl.scrollTop + messagesEl.clientHeight);
      return distance <= threshold;
    }

    function scrollToBottom(smooth) {
      if (smooth) {
        messagesEl.scrollTo({ top: messagesEl.scrollHeight, behavior: 'smooth' });
        return;
      }
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function saveHistory(role, content) {
      var history;
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        history = Array.isArray(JSON.parse(raw)) ? JSON.parse(raw) : [];
      } catch (e) {
        history = [];
      }

      history.push({ role: role, content: content });

      if (history.length > 50) {
        history = history.slice(history.length - 50);
      }

      localStorage.setItem(STORAGE_KEY, JSON.stringify(history));
    }

    function appendMessage(text, role, skipStore) {
      var shouldStick = isNearBottom();

      var row = document.createElement('div');
      row.classList.add('ai-chat-message-row', 'message-enter');

      var bubble = document.createElement('div');
      bubble.classList.add('ai-chat-bubble');

      if (role === 'user') {
        row.classList.add('ai-chat-message-row-user');
        bubble.classList.add('ai-chat-bubble-user');

        var p = document.createElement('p');
        p.classList.add('ai-chat-bubble-text');
        p.textContent = text;
        bubble.appendChild(p);
      } else {
        row.classList.add('ai-chat-message-row-assistant');
        bubble.classList.add('ai-chat-bubble-assistant');
        bubble.innerHTML = text;
      }

      row.appendChild(bubble);
      messagesEl.appendChild(row);

      if (!skipStore) {
        saveHistory(role, text);
      }

      if (shouldStick) {
        requestAnimationFrame(function () {
          scrollToBottom(true);
        });
      }
    }

    function restoreHistory() {
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) {
          return;
        }

        var history = JSON.parse(raw);
        if (!Array.isArray(history) || history.length === 0) {
          return;
        }

        clearMessages();

        history.forEach(function (item) {
          if (!item || typeof item.content !== 'string') {
            return;
          }
          appendMessage(item.content, item.role, true);
        });
      } catch (e) {
      }
    }

    function showTyping() {
      if (typingEl !== null) {
        return;
      }

      var shouldStick = isNearBottom();

      var row = document.createElement('div');
      row.classList.add('ai-chat-message-row', 'ai-chat-message-row-assistant', 'message-enter');

      var bubble = document.createElement('div');
      bubble.classList.add('ai-chat-bubble', 'ai-chat-bubble-assistant');

      var indicator = document.createElement('div');
      indicator.classList.add('typing-indicator');

      var dot1 = document.createElement('span');
      var dot2 = document.createElement('span');
      var dot3 = document.createElement('span');

      indicator.appendChild(dot1);
      indicator.appendChild(dot2);
      indicator.appendChild(dot3);

      bubble.appendChild(indicator);
      row.appendChild(bubble);

      messagesEl.appendChild(row);
      typingEl = row;

      if (shouldStick) {
        requestAnimationFrame(function () {
          scrollToBottom(true);
        });
      }
    }

    function hideTyping() {
      if (typingEl !== null) {
        typingEl.remove();
        typingEl = null;
      }
    }

    function sendToServer(message) {
      var tokenMeta = document.querySelector('meta[name="csrf-token"]');
      var csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';

      return fetch(chatUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ message: message })
      }).then(function (res) {
        return res.json();
      });
    }

    function openChat() {
      windowEl.classList.remove('ai-hidden');
      windowEl.setAttribute('aria-hidden', 'false');
      toggle.classList.add('ai-hidden');

      windowEl.classList.remove('ai-opening');
      windowEl.offsetHeight; // force reflow
      windowEl.classList.add('ai-opening');

      input.focus();
      scrollToBottom(false);
    }

    function closeChat() {
      windowEl.classList.add('ai-hidden');
      windowEl.setAttribute('aria-hidden', 'true');
      toggle.classList.remove('ai-hidden');
    }

    function handleFaqClick(key) {
      var config = FAQ_CONFIG[key];
      if (!config) {
        return;
      }
      appendMessage(config.question, 'user', false);
      appendMessage(config.answer, 'assistant', false);
    }

    toggle.addEventListener('click', function () {
      openChat();
    });

    closeBtn.addEventListener('click', function () {
      closeChat();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') {
        return;
      }
      if (!windowEl.classList.contains('ai-hidden')) {
        closeChat();
      }
    });

    clearBtn.addEventListener('click', function () {
      localStorage.removeItem(STORAGE_KEY);
      clearMessages();
      appendMessage('Xin chào! Tôi có thể giúp gì cho bạn hôm nay?', 'assistant', false);
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var text = input.value.trim();
      if (text === '') {
        return;
      }

      appendMessage(text, 'user', false);
      input.value = '';
      input.disabled = true;
      sendBtn.disabled = true;

      showTyping();

      sendToServer(text)
        .then(function (data) {
          hideTyping();

          if (data && data.ok && data.reply) {
            appendMessage(data.reply, 'assistant', false);
            return;
          }

          if (data && data.message) {
            appendMessage(data.message, 'assistant', false);
            return;
          }

          appendMessage('Xin lỗi, đã có lỗi xảy ra.', 'assistant', false);
        })
        .catch(function () {
          hideTyping();
          appendMessage('Xin lỗi, không kết nối được tới máy chủ.', 'assistant', false);
        })
        .finally(function () {
          input.disabled = false;
          sendBtn.disabled = false;
          input.focus();
        });
    });

    if (faqButtons && faqButtons.length > 0) {
      faqButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var key = btn.getAttribute('data-faq-key');
          if (!key) {
            return;
          }
          handleFaqClick(key);
        });
      });
    }

    restoreHistory();
    scrollToBottom(false);
  });
})();
