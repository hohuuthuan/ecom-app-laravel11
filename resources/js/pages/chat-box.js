(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var widget = document.getElementById('aiChatWidget');
    if (!widget) {
      return;
    }

    var chatUrl   = widget.getAttribute('data-chat-url');
    var toggle    = document.getElementById('aiChatToggle');
    var windowEl  = document.getElementById('aiChatWindow');
    var closeBtn  = document.getElementById('aiChatClose');
    var clearBtn  = document.getElementById('aiChatClearHistory');
    var form      = document.getElementById('aiChatForm');
    var input     = document.getElementById('aiChatInput');
    var messagesEl = document.getElementById('aiChatMessages');
    var sendBtn   = document.getElementById('aiChatSend');
    var typingEl  = null;

    var STORAGE_KEY = 'ai_chat_history_v1';

    if (
      !chatUrl ||
      !toggle ||
      !windowEl ||
      !closeBtn ||
      !clearBtn ||
      !form ||
      !input ||
      !messagesEl ||
      !sendBtn
    ) {
      return;
    }

    function clearMessages() {
      while (messagesEl.firstChild) {
        messagesEl.removeChild(messagesEl.firstChild);
      }
    }

    function scrollToBottom() {
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

      history.push({
        role: role,
        content: content
      });

      if (history.length > 50) {
        history = history.slice(history.length - 50);
      }

      localStorage.setItem(STORAGE_KEY, JSON.stringify(history));
    }

    function appendMessage(text, role, skipStore) {
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

      scrollToBottom();
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

      var row = document.createElement('div');
      row.classList.add(
        'ai-chat-message-row',
        'ai-chat-message-row-assistant',
        'message-enter'
      );

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
      scrollToBottom();
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
        body: JSON.stringify({
          message: message
        })
      }).then(function (res) {
        return res.json();
      });
    }

    toggle.addEventListener('click', function () {
      windowEl.classList.remove('ai-hidden');
      toggle.classList.add('ai-hidden');
      input.focus();
    });

    closeBtn.addEventListener('click', function () {
      windowEl.classList.add('ai-hidden');
      toggle.classList.remove('ai-hidden');
    });

    // Xóa lịch sử chat + reset lời chào
    clearBtn.addEventListener('click', function () {
      localStorage.removeItem(STORAGE_KEY);
      clearMessages();

      appendMessage(
        'Xin chào! Tôi có thể giúp gì cho bạn hôm nay?',
        'assistant',
        false
      );
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
          } else if (data && data.message) {
            appendMessage(data.message, 'assistant', false);
          } else {
            appendMessage('Xin lỗi, đã có lỗi xảy ra.', 'assistant', false);
          }
        })
        .catch(function () {
          hideTyping();
          appendMessage(
            'Xin lỗi, không kết nối được tới máy chủ.',
            'assistant',
            false
          );
        })
        .finally(function () {
          input.disabled = false;
          sendBtn.disabled = false;
          input.focus();
        });
    });

    restoreHistory();
    scrollToBottom();
  });
})();
