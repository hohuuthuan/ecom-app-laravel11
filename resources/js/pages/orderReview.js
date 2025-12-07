document.addEventListener('DOMContentLoaded', function () {
  var wrapper = document.querySelector('.review-products-wrapper');
  if (!wrapper) {
    return;
  }

  function getMessageBox(form) {
    var box = form.querySelector('.js-review-message');
    if (!box) {
      box = document.createElement('div');
      box.className = 'review-message-box js-review-message';
      form.insertBefore(box, form.firstChild);
    }
    return box;
  }

  function clearMessage(form) {
    var box = form.querySelector('.js-review-message');
    if (!box) {
      return;
    }
    box.className = 'review-message-box js-review-message d-none';
    box.innerHTML = '';
  }

  function showMessage(form, type, messages) {
    var box = getMessageBox(form);
    var list = Array.isArray(messages) ? messages : [messages];

    box.className =
      'review-message-box js-review-message alert ' +
      (type === 'success' ? 'alert-success' : 'alert-danger');

    box.innerHTML =
      '<ul class="mb-0">' +
      list
        .filter(function (m) { return m; })
        .map(function (m) {
          return '<li>' + m + '</li>';
        })
        .join('') +
      '</ul>';
  }

  // Preview ảnh
  wrapper.addEventListener('change', function (event) {
    var input = event.target;
    if (!input.classList.contains('review-upload-input')) {
      return;
    }

    var file = input.files && input.files[0] ? input.files[0] : null;
    var card = input.closest('.review-product-card');
    if (!card) {
      return;
    }

    var previewBox = card.querySelector('.js-review-preview-box');
    var previewImg = previewBox
      ? previewBox.querySelector('.review-upload-preview-img')
      : null;

    if (!previewBox || !previewImg) {
      return;
    }

    if (!file) {
      previewImg.src = '';
      previewBox.classList.add('d-none');
      return;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
      previewImg.src = e.target.result;
      previewBox.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
  });

  // Submit đánh giá bằng AJAX
  wrapper.addEventListener('click', function (event) {
    var btn = event.target.closest('.review-submit-btn');
    if (!btn) {
      return;
    }

    var form = btn.closest('.js-review-item-form');
    if (!form || btn.disabled) {
      return;
    }

    clearMessage(form);

    var clientErrors = [];

    // 1) Rating bắt buộc
    var ratingInput = form.querySelector('input[name="rating"]:checked');
    if (!ratingInput) {
      clientErrors.push('Vui lòng chọn số sao cho sản phẩm này.');
    }

    // 2) Comment bắt buộc + max 2000
    var commentEl = form.querySelector('textarea[name="comment"]');
    if (commentEl) {
      var cmt = commentEl.value.trim();
      if (!cmt) {
        clientErrors.push('Vui lòng nhập nội dung đánh giá.');
      } else if (cmt.length > 2000) {
        clientErrors.push('Nội dung đánh giá tối đa 2000 ký tự.');
      }
    }

    // 3) Ảnh: bắt buộc + validate size/type
    var imgInput = form.querySelector('input[name="image"]');
    if (!imgInput || !imgInput.files || !imgInput.files[0]) {
      clientErrors.push('Vui lòng chọn hình minh hoạ.');
    } else {
      var file = imgInput.files[0];
      if (file.size > 2 * 1024 * 1024) {
        clientErrors.push('Dung lượng ảnh tối đa là 2MB.');
      }
      if (!file.type.match(/^image\//)) {
        clientErrors.push('File tải lên phải là hình ảnh.');
      }
    }

    if (clientErrors.length > 0) {
      showMessage(form, 'error', clientErrors);
      return;
    }

    var action = form.getAttribute('action');
    if (!action) {
      showMessage(form, 'error', 'Không tìm thấy địa chỉ gửi đánh giá.');
      return;
    }

    var formData = new FormData(form);
    var originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Đang gửi...';

    fetch(action, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })
      .then(function (res) {
        return res
          .json()
          .catch(function () {
            return { success: false, message: 'Có lỗi xảy ra, vui lòng thử lại.' };
          })
          .then(function (data) {
            return { status: res.status, data: data };
          });
      })
      .then(function (result) {
        var status = result.status;
        var data = result.data || {};

        // Thành công
        if (status === 200 && data.success) {
          showMessage(form, 'success', data.message || 'Gửi đánh giá thành công.');

          form.querySelectorAll('input, textarea').forEach(function (el) {
            el.disabled = true;
          });

          btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Đã gửi';
          btn.classList.add('btn-success');
          btn.classList.remove('btn-outline-primary', 'btn-primary');

          var card = form.closest('.review-product-card');
          if (card && wrapper.contains(card)) {
            wrapper.appendChild(card);
          }

          return;
        }

        // Lỗi validate
        if (status === 422) {
          var errors = [];
          if (data.errors) {
            Object.keys(data.errors).forEach(function (key) {
              var arr = data.errors[key];
              if (Array.isArray(arr)) {
                arr.forEach(function (msg) {
                  errors.push(msg);
                });
              }
            });
          }
          if (!errors.length && data.message) {
            errors.push(data.message);
          }
          if (!errors.length) {
            errors.push('Dữ liệu không hợp lệ, vui lòng kiểm tra lại.');
          }
          showMessage(form, 'error', errors);
          btn.disabled = false;
          btn.innerHTML = originalHtml;
          return;
        }

        // Các lỗi khác (404, 500, ...)
        var msg = data.message || 'Có lỗi xảy ra, vui lòng thử lại.';
        showMessage(form, 'error', msg);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      })
      .catch(function () {
        showMessage(form, 'error', 'Không thể kết nối tới máy chủ, vui lòng thử lại.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      });
  });
});
