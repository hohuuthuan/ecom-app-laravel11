document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('warehouse-import-form');
  if (!form) {
    return;
  }

  // =========================================================
  // 1. HEADER: CHỌN NGÀY THÁNG NĂM PHIẾU NHẬP (Giữ nguyên)
  // =========================================================
  (function () {
    var dateWrapper = form.querySelector('.import-header-date');
    if (!dateWrapper) {
      return;
    }

    var toggle = dateWrapper.querySelector('.import-date-toggle');
    var input = dateWrapper.querySelector('.import-date-input');
    var display = dateWrapper.querySelector('.import-date-display');

    if (!toggle || !input || !display) {
      return;
    }

    function formatDateLabel(value) {
      if (!value) {
        return '(<span class="text-danger">*</span>) Ngày... Tháng... Năm...';
      }

      var parts = String(value).split('-');
      if (parts.length !== 3) {
        return '(<span class="text-danger">*</span>) Ngày... Tháng... Năm...';
      }

      var year = parts[0];
      var month = parts[1];
      var day = parts[2];

      return '(<span class="text-danger">*</span>) Ngày ' +
        Number(day) +
        ' Tháng ' +
        Number(month) +
        ' Năm ' +
        year;
    }

    toggle.addEventListener('click', function () {
      if (typeof input.showPicker === 'function') {
        input.showPicker();
      } else {
        input.focus();
        input.click();
      }
    });

    input.addEventListener('change', function () {
      display.innerHTML = formatDateLabel(input.value);
    });

    if (input.value) {
      display.innerHTML = formatDateLabel(input.value);
    }
  })();

  // =========================================================
  // 2. BẢNG CHI TIẾT HÀNG HÓA: NHÀ XUẤT BẢN & SẢN PHẨM
  // =========================================================
  (function () {
    var table = document.querySelector('#warehouse-import-form .import-table');
    if (!table) {
      return;
    }
    var tbody = table.querySelector('tbody');
    var publisherSelect = document.getElementById('publisher-select');
    if (!publisherSelect) {
      return;
    }

    // --- CẤU HÌNH ---
    var productsUrl = publisherSelect.getAttribute('data-products-url');
    var currentPublisherId = publisherSelect.value || '';
    var currentProducts = [];

    // --- HELPER FUNCTION ---
    function getAllRows() {
      return tbody.querySelectorAll('tr');
    }

    // Chỉ dùng cho select sản phẩm, không phụ thuộc class setupSelect2
    function initProductSelect(selectElement) {
      $(selectElement).select2({ width: '100%' });
    }

    // Helper: Đánh lại index sau khi xóa
    function reIndexRows() {
      var rows = getAllRows();
      rows.forEach(function (row, index) {
        row.querySelector('td:first-child').textContent = index + 1;
        var inputs = row.querySelectorAll('[name]');
        inputs.forEach(function (el) {
          var name = el.getAttribute('name');
          if (name) {
            el.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
          }
        });
      });
    }

    // Helper: Đảm bảo nút xóa tồn tại
    function ensureRemoveButton(row) {
      var actionsWrapper = row.querySelector('.import-actions-wrapper');
      if (!actionsWrapper) {
        return;
      }
      if (!actionsWrapper.querySelector('.import-row-remove')) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm import-btn-icon import-btn-remove import-row-remove ms-1';
        btn.title = 'Xóa dòng';
        btn.innerHTML = '<i class="bi bi-dash-lg"></i>';
        actionsWrapper.appendChild(btn);
      }
    }

    // Ẩn select + message "Bạn cần chọn nhà xuất bản"
    function hideSelectAndShowMessage(selectElement) {
      var row = selectElement.closest('tr');
      if (!row) {
        return;
      }

      var $el = $(selectElement);
      var message = row.querySelector('.import-product-message');

      // Huỷ select2 nếu đã init
      if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
      }

      // Xoá mọi container select2 đã bị clone
      row.querySelectorAll('.select2-container').forEach(function (c) {
        c.remove();
      });

      // Không cho script global init lại
      selectElement.classList.remove('setupSelect2');

      selectElement.classList.add('d-none');
      selectElement.innerHTML = '<option value="">Chọn sản phẩm</option>';
      selectElement.value = '';
      selectElement.removeAttribute('data-old-value');
      selectElement.removeAttribute('data-select2-id');
      selectElement.classList.remove('select2-hidden-accessible');
      selectElement.classList.remove('is-invalid');

      if (message) {
        message.classList.remove('d-none');
      }

      var codeSpan = row.querySelector('.import-product-code');
      if (codeSpan) {
        codeSpan.textContent = 'Mã sản phẩm';
      }
      var unitSpan = row.querySelector('.import-product-unit');
      if (unitSpan) {
        unitSpan.textContent = 'Đơn vị tính';
      }
    }

    // --- CORE LOGIC: RESET TABLE (Chạy khi đổi NXB) ---
    function resetItemsTable() {
      var rows = getAllRows();
      for (var i = rows.length - 1; i > 0; i--) {
        rows[i].remove();
      }

      var firstRow = tbody.querySelector('tr');
      if (firstRow) {
        var inputs = firstRow.querySelectorAll('input');
        inputs.forEach(function (input) {
          input.value = '';
        });

        var codeSpan = firstRow.querySelector('.import-product-code');
        if (codeSpan) {
          codeSpan.textContent = 'Mã sản phẩm';
        }
        var unitSpan = firstRow.querySelector('.import-product-unit');
        if (unitSpan) {
          unitSpan.textContent = 'Đơn vị tính';
        }

        var select = firstRow.querySelector('.import-product-select');
        if (select) {
          hideSelectAndShowMessage(select);
        }

        var stt = firstRow.querySelector('td:first-child');
        if (stt) {
          stt.textContent = '1';
        }
      }
    }

    function handleProductSelectChange(select) {
      var row = select.closest('tr');
      if (!row) {
        return;
      }
      var productId = select.value;
      var codeSpan = row.querySelector('.import-product-code');
      var unitSpan = row.querySelector('.import-product-unit');

      var product = currentProducts.find(function (p) {
        return String(p.id) === String(productId);
      });

      if (codeSpan) {
        codeSpan.textContent = product ? (product.code || '') : 'Mã sản phẩm';
      }
      if (unitSpan) {
        unitSpan.textContent = product ? (product.unit || '') : 'Đơn vị tính';
      }
    }

    // --- CORE LOGIC: ÁP DỤNG LIST SẢN PHẨM CHO TẤT CẢ SELECT ---
    function applyProductsToAllSelects() {
      var selects = tbody.querySelectorAll('.import-product-select');

      selects.forEach(function (selectElement, index) {
        var row = selectElement.closest('tr');
        var message = row ? row.querySelector('.import-product-message') : null;
        var $el = $(selectElement);

        // CHƯA CHỌN NXB -> ẨN HOÀN TOÀN SELECT
        if (!currentPublisherId) {
          hideSelectAndShowMessage(selectElement);
          return;
        }

        // ĐÃ CHỌN NXB -> HIỆN SELECT, ẨN MESSAGE
        if (message) {
          message.classList.add('d-none');
        }
        selectElement.classList.remove('d-none');

        // Giá trị hiện tại của select (giữ lại khi reload / old())
        var oldValueAttr = selectElement.getAttribute('data-old-value');
        var currentValue = selectElement.value;
        var valueToSet = currentValue || oldValueAttr || '';

        // Tập sản phẩm đã được chọn ở CÁC DÒNG TRƯỚC
        var usedIds = [];
        for (var i = 0; i < index; i++) {
          var prev = selects[i];
          var prevVal = prev.value || prev.getAttribute('data-old-value') || '';
          if (prevVal) {
            usedIds.push(String(prevVal));
          }
        }

        // Reset options
        selectElement.innerHTML = '<option value="">Chọn sản phẩm</option>';

        // Chỉ add sản phẩm CHƯA được dùng ở dòng trước
        currentProducts.forEach(function (p) {
          var idStr = String(p.id);
          if (usedIds.indexOf(idStr) !== -1) {
            // sản phẩm đã chọn ở dòng trước -> không add vào select này
            return;
          }
          var option = new Option(p.title, p.id, false, false);
          selectElement.add(option);
        });

        // Chỉ cho chọn lại valueToSet nếu:
        //  - sản phẩm đó có trong currentProducts
        //  - và CHƯA bị dùng ở dòng trước
        var exists = (
          valueToSet &&
          usedIds.indexOf(String(valueToSet)) === -1 &&
          currentProducts.some(function (p) {
            return String(p.id) === String(valueToSet);
          })
        );

        if (exists) {
          selectElement.value = valueToSet;
          selectElement.setAttribute('data-old-value', valueToSet);
        } else {
          selectElement.value = '';
          selectElement.removeAttribute('data-old-value');
        }

        // Init Select2 nếu chưa có
        if (!$el.hasClass('select2-hidden-accessible')) {
          initProductSelect(selectElement);
        }
        $el.val(selectElement.value).trigger('change.select2');

        handleProductSelectChange(selectElement);
      });
    }


    // --- AJAX LOAD PRODUCTS ---
    function loadProductsForPublisher(id) {
      if (!id) {
        currentProducts = [];
        applyProductsToAllSelects();
        return;
      }

      var url = productsUrl + '?publisher_id=' + id;
      fetch(url)
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          currentProducts = data.items || [];
          applyProductsToAllSelects();
        })
        .catch(function (err) {
          console.error(err);
        });
    }

    // --- EVENT HANDLERS ---
    $(document).on('change', '#publisher-select', function () {
      var newVal = this.value;
      if (newVal !== currentPublisherId) {
        currentPublisherId = newVal;
        resetItemsTable();
        loadProductsForPublisher(newVal);
      }
    });

    $(document).on('change', '.import-product-select', function () {
      handleProductSelectChange(this);
      this.setAttribute('data-old-value', this.value);
    });

    tbody.addEventListener('click', function (e) {
      var addBtn = e.target.closest('.import-row-add');
      if (addBtn) {
        var rows = getAllRows();
        var lastRow = rows[rows.length - 1];
        var newRow = lastRow.cloneNode(true);

        var clonedSelect = newRow.querySelector('.import-product-select');
        if (clonedSelect) {
          var selectCell = clonedSelect.closest('.import-product-cell');
          if (selectCell) {
            selectCell.querySelectorAll('.select2-container').forEach(function (container) {
              container.remove();
            });
          }

          clonedSelect.value = '';
          clonedSelect.removeAttribute('data-old-value');
          clonedSelect.removeAttribute('data-select2-id');
          clonedSelect.classList.remove('select2-hidden-accessible');
          clonedSelect.classList.remove('is-invalid');
          clonedSelect.classList.remove('d-none');
        }

        var message = newRow.querySelector('.import-product-message');
        if (message) {
          message.classList.add('d-none');
        }

        var inputs = newRow.querySelectorAll('input');
        inputs.forEach(function (inp) {
          inp.value = '';
        });

        var codeSpan = newRow.querySelector('.import-product-code');
        if (codeSpan) {
          codeSpan.textContent = 'Mã sản phẩm';
        }
        var unitSpan = newRow.querySelector('.import-product-unit');
        if (unitSpan) {
          unitSpan.textContent = 'Đơn vị tính';
        }

        var rowsCount = rows.length;
        newRow.innerHTML = newRow.innerHTML.replace(/items\[\d+\]/g, 'items[' + rowsCount + ']');

        var stt = newRow.querySelector('td:first-child');
        if (stt) {
          stt.textContent = rowsCount + 1;
        }

        tbody.appendChild(newRow);

        if (clonedSelect) {
          initProductSelect(clonedSelect);
          applyProductsToAllSelects();
        }

        ensureRemoveButton(newRow);
      }

      var removeBtn = e.target.closest('.import-row-remove');
      if (removeBtn) {
        var row = removeBtn.closest('tr');
        if (getAllRows().length > 1) {
          row.remove();
          reIndexRows();
        }
      }
    });

    // --- KHỞI CHẠY LẦN ĐẦU ---
    if (currentPublisherId) {
      loadProductsForPublisher(currentPublisherId);
    } else {
      applyProductsToAllSelects(); // sẽ ẩn sạch select + container
    }
  })();

  // =========================================================
  // 3. ĐỊNH DẠNG GIÁ, KIỂM TRA SỐ LƯỢNG, TÍNH TỔNG (Giữ nguyên)
  // =========================================================
  (function () {
    var table = document.querySelector('#warehouse-import-form .import-table');
    if (!table) {
      return;
    }

    var tbody = table.querySelector('tbody');
    if (!tbody) {
      return;
    }

    var totalInput = form.querySelector('.import-total-input');
    if (!totalInput) {
      return;
    }

    function parseNumber(value) {
      var cleaned = String(value || '').replace(/[^\d]/g, '').trim();
      if (!cleaned) {
        return NaN;
      }
      return Number(cleaned);
    }

    function formatNumber(num) {
      var n = Number(num);
      if (!isFinite(n)) {
        return '';
      }
      return n.toLocaleString('vi-VN');
    }

    function setHiddenPrice(row, rawDigits) {
      var hidden = row.querySelector('.import-price-value');
      if (!hidden) {
        return;
      }
      var cleaned = String(rawDigits || '').replace(/[^\d]/g, '');
      hidden.value = cleaned ? String(Number(cleaned)) : '';
    }

    function validateQtyRow(row) {
      var qtyDocInput = row.querySelector('input[name^="items"][name$="[qty_document]"]');
      var qtyRealInput = row.querySelector('input[name^="items"][name$="[qty_real]"]');
      if (!qtyDocInput || !qtyRealInput) {
        return;
      }

      var qtyDoc = parseNumber(qtyDocInput.value);
      var qtyReal = parseNumber(qtyRealInput.value);

      qtyDocInput.classList.remove('is-invalid');
      qtyRealInput.classList.remove('is-invalid');

      if (isNaN(qtyDoc) && isNaN(qtyReal)) {
        return;
      }

      if (isNaN(qtyDoc) || isNaN(qtyReal) || qtyDoc !== qtyReal) {
        qtyDocInput.classList.add('is-invalid');
        qtyRealInput.classList.add('is-invalid');
      }
    }

    function updateTotal() {
      var rows = tbody.querySelectorAll('tr');
      var sum = 0;

      rows.forEach(function (row) {
        var hiddenPrice = row.querySelector('.import-price-value');
        var qtyRealInput = row.querySelector('input[name^="items"][name$="[qty_real]"]');
        if (!hiddenPrice || !qtyRealInput) {
          return;
        }

        var price = parseNumber(hiddenPrice.value);
        var qtyReal = parseNumber(qtyRealInput.value);

        if (!isNaN(price) && !isNaN(qtyReal)) {
          sum += price * qtyReal;
        }
      });

      totalInput.value = formatNumber(sum) + ' VND';
    }

    function handlePriceTyping(input) {
      var row = input.closest('tr');
      if (!row) {
        return;
      }

      var raw = String(input.value || '');
      var selectionStart = input.selectionStart;
      if (typeof selectionStart !== 'number') {
        selectionStart = raw.length;
      }

      var digitsBeforeCaret = raw.slice(0, selectionStart).replace(/[^\d]/g, '').length;

      var digits = raw.replace(/[^\d]/g, '');
      if (!digits) {
        input.value = '';
        setHiddenPrice(row, '');
        updateTotal();
        return;
      }

      var num = Number(digits);
      if (!isFinite(num)) {
        input.value = '';
        setHiddenPrice(row, '');
        updateTotal();
        return;
      }

      var formatted = formatNumber(num);
      input.value = formatted;
      setHiddenPrice(row, digits);

      var newPos = formatted.length;
      var digitCount = 0;
      for (var i = 0; i < formatted.length; i++) {
        if (/\d/.test(formatted.charAt(i))) {
          digitCount++;
          if (digitCount === digitsBeforeCaret) {
            newPos = i + 1;
            break;
          }
        }
      }

      try {
        input.setSelectionRange(newPos, newPos);
      } catch (e) { }

      updateTotal();
    }

    tbody.addEventListener('input', function (event) {
      var target = event.target;
      if (!(target instanceof HTMLInputElement)) {
        return;
      }

      if (target.classList.contains('import-price-display')) {
        handlePriceTyping(target);
        return;
      }

      var nameAttr = target.getAttribute('name') || '';
      var isQtyDoc = /\[qty_document\]$/.test(nameAttr);
      var isQtyReal = /\[qty_real\]$/.test(nameAttr);

      if (!isQtyDoc && !isQtyReal) {
        return;
      }

      var row = target.closest('tr');
      if (row) {
        validateQtyRow(row);
      }

      if (isQtyReal) {
        updateTotal();
      }
    });

    tbody.addEventListener('blur', function (event) {
      var target = event.target;
      if (!(target instanceof HTMLInputElement)) {
        return;
      }

      if (target.classList.contains('import-price-display')) {
        var row = target.closest('tr');
        var num = parseNumber(target.value);
        var formatted = isNaN(num) ? '' : formatNumber(num);
        target.value = formatted;
        if (row) {
          setHiddenPrice(row, isNaN(num) ? '' : String(num));
        }
        updateTotal();
        return;
      }

      var nameAttr = target.getAttribute('name') || '';
      var isQtyDoc = /\[qty_document\]$/.test(nameAttr);
      var isQtyReal = /\[qty_real\]$/.test(nameAttr);

      if (!isQtyDoc && !isQtyReal) {
        return;
      }

      var row2 = target.closest('tr');
      if (row2) {
        validateQtyRow(row2);
      }

      if (isQtyReal) {
        updateTotal();
      }
    }, true);

    tbody.addEventListener('click', function (event) {
      var addBtn = event.target.closest('.import-row-add');
      var removeBtn = event.target.closest('.import-row-remove');

      if (addBtn || removeBtn) {
        setTimeout(function () {
          updateTotal();
        }, 0);
      }
    });

    form.addEventListener('submit', function (event) {
      var rows = tbody.querySelectorAll('tr');
      var hasError = false;

      rows.forEach(function (row) {
        validateQtyRow(row);

        var qtyDocInput = row.querySelector('input[name^="items"][name$="[qty_document]"]');
        var qtyRealInput = row.querySelector('input[name^="items"][name$="[qty_real]"]');

        if (
          qtyDocInput &&
          qtyRealInput &&
          (qtyDocInput.classList.contains('is-invalid') || qtyRealInput.classList.contains('is-invalid'))
        ) {
          hasError = true;
        }
      });

      if (hasError) {
        event.preventDefault();
        alert('Số lượng theo chứng từ và thực nhập phải là số và bằng nhau ở tất cả các dòng.');
      }
    });

    updateTotal();
  })();
});
