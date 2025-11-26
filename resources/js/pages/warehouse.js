document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('warehouse-import-form');
  if (!form) {
    return;
  }

  // =========================================================
  // 1. HEADER: CHỌN NGÀY THÁNG NĂM PHIẾU NHẬP
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
    if (!tbody) {
      return;
    }

    var publisherSelect = document.getElementById('publisher-select');
    if (!publisherSelect) {
      return;
    }

    var productsUrl = publisherSelect.getAttribute('data-products-url');
    var currentPublisherId = '';
    var currentProducts = [];

    // -----------------------------
    // Helpers
    // -----------------------------
    function getAllRows() {
      return tbody.querySelectorAll('tr');
    }

    function getAllProductSelects() {
      return tbody.querySelectorAll('.import-product-select');
    }

    // Init Select2 cho context (body hoặc row mới)
    function initSelect2ForContext(ctx) {
      if (!window.jQuery) {
        return;
      }

      if (window.HHT && typeof window.HHT.select2 === 'function') {
        window.HHT.select2(ctx);
        return;
      }

      if (!jQuery.fn.select2) {
        return;
      }

      var $root = ctx ? jQuery(ctx) : jQuery(document);
      $root.find('.setupSelect2').each(function () {
        var $el = jQuery(this);
        if ($el.hasClass('select2-hidden-accessible')) {
          return;
        }
        var $parent = $el.closest('.modal');
        $el.select2({
          dropdownParent: $parent.length ? $parent : jQuery(document.body),
          width: $el.data('width') || 'resolve'
        });
      });
    }

    function resetProductSelect(selectElement) {
      selectElement.innerHTML = '<option value="">Chọn sản phẩm</option>';
      selectElement.value = '';

      if (
        window.jQuery &&
        jQuery.fn.select2 &&
        jQuery(selectElement).hasClass('select2-hidden-accessible')
      ) {
        jQuery(selectElement).val('').trigger('change.select2');
      }
    }

    function resetRowDisplay(row) {
      var codeSpan = row.querySelector('.import-product-code');
      if (codeSpan) {
        codeSpan.textContent = 'Mã sản phẩm';
      }
      var unitSpan = row.querySelector('.import-product-unit');
      if (unitSpan) {
        unitSpan.textContent = 'Đơn vị tính';
      }
    }

    function resetRowInputs(row) {
      var inputs = row.querySelectorAll('input[type="number"], input[type="text"], input[type="hidden"]');
      inputs.forEach(function (input) {
        input.value = '';
      });
    }

    // cell sản phẩm: chỉ còn span "cần chọn NXB"
    function showMessageNeedPublisher(row) {
      var cell = row.querySelector('.import-product-cell');
      if (!cell) {
        return;
      }
      cell.innerHTML = '<span class="import-product-message text-danger">Bạn cần chọn nhà xuất bản</span>';
    }

    // cell sản phẩm: chỉ còn span "chưa có sách"
    function showMessageNoBooks(row) {
      var cell = row.querySelector('.import-product-cell');
      if (!cell) {
        return;
      }
      cell.innerHTML = '<span class="import-product-message text-danger">Chưa có sách</span>';
    }

    // cell sản phẩm: hiển thị select (không còn span)
    function showSelectForRow(row) {
      var cell = row.querySelector('.import-product-cell');
      if (!cell) {
        return;
      }

      // Xoá container .select2 nếu bị clone từ row trước
      var oldContainer = cell.querySelector('.select2');
      if (oldContainer && oldContainer.parentNode) {
        oldContainer.parentNode.removeChild(oldContainer);
      }

      var selectElement = cell.querySelector('select.import-product-select');
      if (!selectElement) {
        selectElement = document.createElement('select');
        selectElement.className = 'form-select form-select-sm setupSelect2 import-product-select';
        selectElement.name = 'temp_product_id'; // reindexRows sẽ sửa lại
      } else {
        // Bỏ dấu vết Select2 trên select đã clone
        if (window.jQuery && jQuery.fn.select2) {
          var $sel = jQuery(selectElement);
          try {
            if ($sel.data('select2')) {
              $sel.select2('destroy');
            }
          } catch (e) {
            console.error(e);
          }
        }
        selectElement.classList.remove('select2-hidden-accessible');
        selectElement.removeAttribute('data-select2-id');
        selectElement.removeAttribute('aria-hidden');
        selectElement.style.removeProperty('display');

        var options = selectElement.querySelectorAll('option');
        options.forEach(function (opt) {
          opt.removeAttribute('data-select2-id');
        });
      }

      resetProductSelect(selectElement);
      cell.innerHTML = '';
      cell.appendChild(selectElement);
    }

    function rebuildFirstRowActions(row) {
      var actionsCell = row.querySelector('.import-actions-cell');
      if (!actionsCell) {
        return;
      }

      var wrapper = actionsCell.querySelector('.import-actions-wrapper');
      if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.className = 'import-actions-wrapper';
        actionsCell.innerHTML = '';
        actionsCell.appendChild(wrapper);
      } else {
        wrapper.innerHTML = '';
      }

      var addBtn = document.createElement('button');
      addBtn.type = 'button';
      addBtn.className = 'btn btn-sm import-btn-icon import-btn-add import-row-add';
      addBtn.title = 'Thêm dòng';
      addBtn.innerHTML = '<i class="bi bi-plus-lg"></i>';

      wrapper.appendChild(addBtn);
    }

    function ensureRowActionsForNonFirstRow(row) {
      var actionsCell = row.querySelector('.import-actions-cell');
      if (!actionsCell) {
        return;
      }

      var wrapper = actionsCell.querySelector('.import-actions-wrapper');
      if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.className = 'import-actions-wrapper';
        actionsCell.innerHTML = '';
        actionsCell.appendChild(wrapper);
      }

      wrapper.innerHTML = '';

      var addBtn = document.createElement('button');
      addBtn.type = 'button';
      addBtn.className = 'btn btn-sm import-btn-icon import-btn-add import-row-add';
      addBtn.title = 'Thêm dòng';
      addBtn.innerHTML = '<i class="bi bi-plus-lg"></i>';

      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn-sm import-btn-icon import-btn-remove import-row-remove';
      removeBtn.title = 'Xóa dòng';
      removeBtn.innerHTML = '<i class="bi bi-dash-lg"></i>';

      wrapper.appendChild(addBtn);
      wrapper.appendChild(removeBtn);
    }

    function reindexRows() {
      var rows = getAllRows();
      rows.forEach(function (row, index) {
        var sttCell = row.querySelector('td:first-child');
        if (sttCell) {
          sttCell.textContent = String(index + 1);
        }

        var fields = row.querySelectorAll('input[name], select[name], textarea[name]');
        fields.forEach(function (field) {
          var name = field.getAttribute('name');
          if (!name) {
            return;
          }
          var newName = name.replace(/items\[\d+]/, 'items[' + String(index) + ']');
          field.setAttribute('name', newName);
        });

        if (index === 0) {
          rebuildFirstRowActions(row);
        } else {
          ensureRowActionsForNonFirstRow(row);
        }
      });
    }

    function resetItemsTable() {
      var rows = getAllRows();
      rows.forEach(function (row, index) {
        if (index > 0) {
          row.remove();
        }
      });

      var firstRow = tbody.querySelector('tr');
      if (!firstRow) {
        return;
      }

      resetRowDisplay(firstRow);
      resetRowInputs(firstRow);
      showMessageNeedPublisher(firstRow);
      reindexRows();
    }

    // Đổ danh sách sản phẩm (theo NXB) vào tất cả các select
    function applyProductsToAllSelects() {
      var rows = getAllRows();

      // Không có sách
      if (!currentProducts.length) {
        rows.forEach(function (row) {
          resetRowDisplay(row);
          showMessageNoBooks(row);
        });
        return;
      }

      // Có sách → thay span bằng select ở tất cả các hàng
      rows.forEach(function (row) {
        showSelectForRow(row);
        resetRowDisplay(row);
      });

      var selects = getAllProductSelects();

      selects.forEach(function (selectElement) {
        var previousValue = selectElement.value;

        resetProductSelect(selectElement);

        currentProducts.forEach(function (product) {
          var opt = document.createElement('option');
          opt.value = product.id;
          opt.textContent = product.title || '';
          selectElement.appendChild(opt);
        });

        var valueToSet = '';
        if (previousValue) {
          var found = currentProducts.some(function (product) {
            return String(product.id) === String(previousValue);
          });
          if (found) {
            valueToSet = previousValue;
          }
        }
        selectElement.value = valueToSet;
      });

      // Init Select2 cho toàn bộ tbody
      initSelect2ForContext(tbody);

      // Đồng bộ lại value nếu đang có
      if (window.jQuery && jQuery.fn.select2) {
        getAllProductSelects().forEach(function (selectElement) {
          var val = selectElement.value;
          if (jQuery(selectElement).hasClass('select2-hidden-accessible')) {
            jQuery(selectElement).val(val).trigger('change.select2');
          }
        });
      }
    }

    // Gọi API lấy sản phẩm theo nhà xuất bản
    function loadProductsForPublisher(publisherId) {
      currentProducts = [];

      if (!productsUrl || !publisherId) {
        return;
      }

      var url = productsUrl + '?publisher_id=' + encodeURIComponent(publisherId);

      fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        }
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(function (data) {
          if (!data || !Array.isArray(data.items)) {
            currentProducts = [];
          } else {
            currentProducts = data.items;
          }
          applyProductsToAllSelects();
        })
        .catch(function (error) {
          console.error('Error loading products by publisher:', error);
          currentProducts = [];
          applyProductsToAllSelects();
        });
    }

    // Đổi NXB: reset bảng + load lại sản phẩm
    function handlePublisherChange(value) {
      if (!value) {
        currentPublisherId = '';
        currentProducts = [];
        resetItemsTable();
        return;
      }

      if (value !== currentPublisherId) {
        currentPublisherId = value;
        currentProducts = [];
        resetItemsTable();
        loadProductsForPublisher(currentPublisherId);
      }
    }

    // Bind change cho nhà xuất bản (có Select2)
    if (window.jQuery) {
      jQuery(document).on('change', '#publisher-select', function () {
        handlePublisherChange(this.value);
      });
    } else {
      publisherSelect.addEventListener('change', function () {
        handlePublisherChange(publisherSelect.value);
      });
    }

    // HÀM dùng lại: refil Mã SP + ĐVT khi chọn sản phẩm
    function handleProductSelectChange(select) {
      if (!select || !select.classList.contains('import-product-select')) {
        return;
      }

      var productId = select.value;
      var row = select.closest('tr');
      if (!row) {
        return;
      }

      var codeSpan = row.querySelector('.import-product-code');
      var unitSpan = row.querySelector('.import-product-unit');

      if (!productId) {
        if (codeSpan) {
          codeSpan.textContent = 'Mã sản phẩm';
        }
        if (unitSpan) {
          unitSpan.textContent = 'Đơn vị tính';
        }
        return;
      }

      var foundProduct = currentProducts.find(function (product) {
        return String(product.id) === String(productId);
      });

      if (!foundProduct) {
        if (codeSpan) {
          codeSpan.textContent = 'Mã sản phẩm';
        }
        if (unitSpan) {
          unitSpan.textContent = 'Đơn vị tính';
        }
        return;
      }

      if (codeSpan) {
        codeSpan.textContent = foundProduct.code || 'Mã sản phẩm';
      }
      if (unitSpan) {
        unitSpan.textContent = foundProduct.unit || 'Đơn vị tính';
      }
    }

    // Khi chọn sản phẩm: auto điền mã & đơn vị (hỗ trợ Select2)
    if (window.jQuery) {
      jQuery(tbody).on('change', '.import-product-select', function () {
        handleProductSelectChange(this);
      });
    } else {
      tbody.addEventListener('change', function (event) {
        var target = event.target;
        handleProductSelectChange(target);
      });
    }

    // Nút cộng/trừ dòng
    tbody.addEventListener('click', function (event) {
      var addButton = event.target.closest('.import-row-add');
      if (addButton) {
        var rows = getAllRows();
        var lastRow = rows[rows.length - 1];
        var newRow = lastRow.cloneNode(true);

        resetRowInputs(newRow);
        resetRowDisplay(newRow);

        if (!currentPublisherId) {
          showMessageNeedPublisher(newRow);
        } else if (!currentProducts.length) {
          showMessageNoBooks(newRow);
        } else {
          showSelectForRow(newRow);
          var selectElement = newRow.querySelector('.import-product-select');
          if (selectElement) {
            resetProductSelect(selectElement);
            currentProducts.forEach(function (product) {
              var opt = document.createElement('option');
              opt.value = product.id;
              opt.textContent = product.title || '';
              selectElement.appendChild(opt);
            });
          }
        }

        tbody.appendChild(newRow);
        reindexRows();

        // Init Select2 chỉ cho row mới
        initSelect2ForContext(newRow);

        return;
      }

      var removeButton = event.target.closest('.import-row-remove');
      if (removeButton) {
        var row = removeButton.closest('tr');
        if (!row) {
          return;
        }

        var allRows = getAllRows();
        if (allRows.length <= 1) {
          resetItemsTable();
        } else {
          row.remove();
          reindexRows();
        }
      }
    });

    // Khởi tạo: 1 hàng, chỉ span "Bạn cần chọn nhà xuất bản"
    resetItemsTable();
  })();

  // =========================================================
  // 3. ĐỊNH DẠNG GIÁ REAL-TIME, VALIDATE SỐ LƯỢNG, TÍNH TỔNG TIỀN
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

    // parse "1.000.000" -> 1000000 (NaN nếu rỗng/không hợp lệ)
    function parseNumber(value) {
      var cleaned = String(value || '').replace(/[^\d]/g, '').trim();
      if (!cleaned) {
        return NaN;
      }
      return Number(cleaned);
    }

    // format 1000000 -> "1.000.000"
    function formatNumber(num) {
      var n = Number(num);
      if (!isFinite(n)) {
        return '';
      }
      return n.toLocaleString('vi-VN');
    }

    // Cập nhật input hidden giá từ số thô
    function setHiddenPrice(row, rawDigits) {
      var hidden = row.querySelector('.import-price-value');
      if (!hidden) {
        return;
      }
      var cleaned = String(rawDigits || '').replace(/[^\d]/g, '');
      hidden.value = cleaned ? String(Number(cleaned)) : '';
    }

    // Validate 2 cột số lượng trong 1 dòng: phải là số và bằng nhau
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

    // Tính tổng: sum(price_hidden * qty_real)
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

    // Xử lý gõ giá real-time: format + giữ con trỏ + cập nhật hidden
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
      } catch (e) {
      }

      updateTotal();
    }

    // Lắng nghe input trong tbody
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

    // Khi blur: cleanup lại giá & số lượng
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

    // Khi thêm / xóa dòng -> cập nhật lại tổng
    tbody.addEventListener('click', function (event) {
      var addBtn = event.target.closest('.import-row-add');
      var removeBtn = event.target.closest('.import-row-remove');

      if (addBtn || removeBtn) {
        setTimeout(function () {
          updateTotal();
        }, 0);
      }
    });

    // Trước khi submit: check lại số lượng
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
