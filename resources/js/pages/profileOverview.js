(function () {
  // ==== RESET MODAL EDIT PROFILE ====
  var profileModalEl = document.getElementById("editProfileModal");
  if (profileModalEl) {
    profileModalEl.addEventListener("hidden.bs.modal", function () {
      profileModalEl.querySelectorAll(".is-invalid").forEach(function (el) {
        el.classList.remove("is-invalid");
      });

      profileModalEl.querySelectorAll(".invalid-feedback").forEach(function (el) {
        el.classList.add("d-none");
      });

      profileModalEl.querySelectorAll(".alert").forEach(function (el) {
        el.classList.add("d-none");
      });

      profileModalEl
        .querySelectorAll("input[name], textarea[name], select[name]")
        .forEach(function (field) {
          var originalValue = field.getAttribute("data-original-value");
          if (originalValue !== null) {
            field.value = originalValue;
          }
        });
    });
  }

  // ==== RESET MODAL UPDATE ĐỊA CHỈ ====
  var updateAddressModalEl = document.getElementById("updateAddressModal");
  if (updateAddressModalEl) {
    updateAddressModalEl.addEventListener("hidden.bs.modal", function () {
      updateAddressModalEl.querySelectorAll(".is-invalid").forEach(function (el) {
        el.classList.remove("is-invalid");
      });

      updateAddressModalEl.querySelectorAll(".invalid-feedback").forEach(function (el) {
        el.classList.add("d-none");
      });

      var addrInput = updateAddressModalEl.querySelector("#updateShippingAddress");
      if (addrInput) {
        addrInput.value = "";
      }

      var provinceSelect = updateAddressModalEl.querySelector("#updateShippingProvince");
      if (provinceSelect) {
        provinceSelect.value = "";
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(provinceSelect).hasClass("select2-hidden-accessible")
        ) {
          jQuery(provinceSelect).val("").trigger("change.select2");
        }
      }

      var wardSelect = updateAddressModalEl.querySelector("#updateShippingWard");
      if (wardSelect) {
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(wardSelect).hasClass("select2-hidden-accessible")
        ) {
          jQuery(wardSelect).val("").trigger("change.select2");
        }
      }

      var noteTextarea = updateAddressModalEl.querySelector("#updateAddressNote");
      if (noteTextarea) {
        noteTextarea.value = "";
      }

      var defaultCheckbox = updateAddressModalEl.querySelector("#updateAddressDefault");
      if (defaultCheckbox) {
        defaultCheckbox.checked = false;
      }

      var hiddenId = updateAddressModalEl.querySelector("#updateAddressId");
      if (hiddenId) {
        hiddenId.value = "";
      }
    });

    // ==== HANDLE ĐỔI TỈNH TRONG MODAL UPDATE (SELECT2) ====
    if (window.jQuery) {
      jQuery(document).on("change", "#updateShippingProvince", function () {
        if (!updateAddressModalEl) {
          return;
        }

        var provinceSelect = this;
        var wardSelect = updateAddressModalEl.querySelector("#updateShippingWard");
        if (!wardSelect) {
          return;
        }

        var provinceId = provinceSelect.value;
        var wardsUrl = provinceSelect.getAttribute("data-wards-url");
        var initialWardId = provinceSelect.getAttribute("data-initial-ward-id") || "";

        provinceSelect.removeAttribute("data-initial-ward-id");

        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(wardSelect).hasClass("select2-hidden-accessible")
        ) {
          jQuery(wardSelect).val("").trigger("change.select2");
        }

        if (!provinceId || !wardsUrl) {
          return;
        }

        wardSelect.innerHTML = '<option value="">Đang tải...</option>';
        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(wardSelect).hasClass("select2-hidden-accessible")
        ) {
          jQuery(wardSelect).val("").trigger("change.select2");
        }

        fetch(wardsUrl + "?province_id=" + encodeURIComponent(provinceId))
          .then(function (res) {
            if (!res.ok) {
              return null;
            }
            return res.json();
          })
          .then(function (data) {
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';

            if (!data || !data.wards) {
              if (
                window.jQuery &&
                jQuery.fn.select2 &&
                jQuery(wardSelect).hasClass("select2-hidden-accessible")
              ) {
                jQuery(wardSelect).val("").trigger("change.select2");
              }
              return;
            }

            data.wards.forEach(function (ward) {
              var opt = document.createElement("option");
              opt.value = ward.id;
              opt.textContent = ward.name_with_type || ward.name;
              if (initialWardId && String(ward.id) === String(initialWardId)) {
                opt.selected = true;
              }
              wardSelect.appendChild(opt);
            });

            if (
              window.jQuery &&
              jQuery.fn.select2 &&
              jQuery(wardSelect).hasClass("select2-hidden-accessible")
            ) {
              if (initialWardId) {
                jQuery(wardSelect).val(initialWardId).trigger("change.select2");
              } else {
                jQuery(wardSelect).val("").trigger("change.select2");
              }
            }
          })
          .catch(function () {
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            if (
              window.jQuery &&
              jQuery.fn.select2 &&
              jQuery(wardSelect).hasClass("select2-hidden-accessible")
            ) {
              jQuery(wardSelect).val("").trigger("change.select2");
            }
          });
      });
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    // ==== CONFIRM XOÁ ĐỊA CHỈ ====
    document.addEventListener("click", function (e) {
      var btn = e.target.closest(".js-address-delete-btn");
      if (!btn) {
        return;
      }

      var form = btn.closest(".js-address-delete-form");
      if (!form) {
        return;
      }

      if (!window.UIConfirm || typeof window.UIConfirm !== "function") {
        if (window.confirm("Bạn có chắc chắn muốn xóa địa chỉ này không?")) {
          form.submit();
        }
        return;
      }

      var message =
        btn.getAttribute("data-confirm-message") ||
        "Bạn có chắc chắn muốn xóa địa chỉ này không?";

      window
        .UIConfirm({
          title: "Xác nhận xoá địa chỉ",
          message: message,
          confirmText: "Xoá",
          cancelText: "Huỷ",
          size: "md",
        })
        .then(function (confirmed) {
          if (confirmed) {
            form.submit();
          }
        });
    });

    // ==== MỞ MODAL CHỈNH SỬA ĐỊA CHỈ ====
    document.addEventListener("click", function (e) {
      var btn = e.target.closest(".address-edit-btn");
      if (!btn) {
        return;
      }

      var modalEl = document.getElementById("updateAddressModal");
      if (!modalEl || !window.bootstrap) {
        return;
      }

      var form = modalEl.querySelector("form");
      var id = btn.getAttribute("data-id") || "";
      var updateUrl = btn.getAttribute("data-update-url") || "";

      if (form && updateUrl) {
        form.action = updateUrl;
      }

      var hiddenId = modalEl.querySelector("#updateAddressId");
      if (hiddenId) {
        hiddenId.value = id;
      }

      var defaultCheckbox = modalEl.querySelector("#updateAddressDefault");
      if (defaultCheckbox) {
        var isDefault = btn.getAttribute("data-default");
        defaultCheckbox.checked = isDefault === "1" || isDefault === "true";
      }

      var addressInput = modalEl.querySelector('input[name="address"]');
      if (addressInput && !addressInput.value) {
        addressInput.value = btn.getAttribute("data-address") || "";
      }

      var noteTextarea = modalEl.querySelector('textarea[name="note"]');
      if (noteTextarea && !noteTextarea.value) {
        noteTextarea.value = btn.getAttribute("data-note") || "";
      }

      var provinceSelect = modalEl.querySelector('select[name="address_province_id"]');
      var wardId = btn.getAttribute("data-ward-id") || "";
      var provinceId = btn.getAttribute("data-province-id") || "";

      if (provinceSelect && provinceId) {
        provinceSelect.value = provinceId;
        provinceSelect.setAttribute("data-initial-ward-id", wardId);

        if (
          window.jQuery &&
          jQuery.fn.select2 &&
          jQuery(provinceSelect).hasClass("select2-hidden-accessible")
        ) {
          jQuery(provinceSelect).val(provinceId).trigger("change.select2");
        }
      }

      var modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    });

    // ==== FILTER ĐƠN HÀNG BẰNG AJAX (TRẠNG THÁI + KHOẢNG NGÀY) ====
    var ordersWrapper = document.querySelector("[data-orders-container]");
    if (!ordersWrapper) {
      return;
    }

    var baseUrl = ordersWrapper.getAttribute("data-orders-url") || window.location.href;
    var tabName = ordersWrapper.getAttribute("data-orders-tab") || "orders";
    var filterButtons = document.querySelectorAll(".orders-filter-btn");
    var perPageSelect = document.querySelector('select[name="per_page_order"]');
    var dateFromInput = document.querySelector(".js-orders-date-from");
    var dateToInput = document.querySelector(".js-orders-date-to");
    var applyDateBtn = document.querySelector(".js-orders-apply-date");
    var clearDateBtn = document.querySelector(".js-orders-clear-date");

    function setActiveButtonFromUrl(url) {
      if (!filterButtons.length) {
        return;
      }
      var urlObj = new URL(url, window.location.origin);
      var currentGroup = urlObj.searchParams.get("status_group") || "";
      filterButtons.forEach(function (btn) {
        var group = btn.getAttribute("data-status-group") || "";
        btn.classList.toggle("active", group === currentGroup);
      });
    }

    function applyDateParams(urlObj) {
      if (dateFromInput && dateFromInput.value) {
        urlObj.searchParams.set("created_from", dateFromInput.value);
      } else {
        urlObj.searchParams.delete("created_from");
      }

      if (dateToInput && dateToInput.value) {
        urlObj.searchParams.set("created_to", dateToInput.value);
      } else {
        urlObj.searchParams.delete("created_to");
      }
    }

    function loadOrders(url) {
      if (!url) {
        return;
      }

      ordersWrapper.classList.add("is-loading");

      fetch(url, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then(function (res) {
          if (!res.ok) {
            throw new Error("Request failed");
          }
          return res.text();
        })
        .then(function (html) {
          ordersWrapper.innerHTML = html;
          ordersWrapper.classList.remove("is-loading");
          setActiveButtonFromUrl(url);
        })
        .catch(function () {
          ordersWrapper.classList.remove("is-loading");
        });
    }

    // Click filter trạng thái
    if (filterButtons.length) {
      filterButtons.forEach(function (btn) {
        btn.addEventListener("click", function () {
          var statusGroup = btn.getAttribute("data-status-group") || "";
          var urlObj = new URL(baseUrl, window.location.origin);

          urlObj.searchParams.set("tab", tabName);
          urlObj.searchParams.delete("page");

          if (statusGroup) {
            urlObj.searchParams.set("status_group", statusGroup);
          } else {
            urlObj.searchParams.delete("status_group");
          }

          if (perPageSelect) {
            urlObj.searchParams.set("per_page_order", perPageSelect.value);
          }

          applyDateParams(urlObj);

          loadOrders(urlObj.toString());
        });
      });
    }

    // Thay đổi số bản ghi / trang
    if (perPageSelect) {
      perPageSelect.addEventListener("change", function (e) {
        e.preventDefault();

        var urlObj = new URL(baseUrl, window.location.origin);
        urlObj.searchParams.set("tab", tabName);
        urlObj.searchParams.delete("page");
        urlObj.searchParams.set("per_page_order", perPageSelect.value);

        var activeBtn = document.querySelector(".orders-filter-btn.active");
        if (activeBtn) {
          var statusGroup = activeBtn.getAttribute("data-status-group") || "";
          if (statusGroup) {
            urlObj.searchParams.set("status_group", statusGroup);
          }
        }

        applyDateParams(urlObj);

        loadOrders(urlObj.toString());
      });
    }

    // Nhấn nút "Lọc" theo ngày
    if (applyDateBtn) {
      applyDateBtn.addEventListener("click", function () {
        var urlObj = new URL(baseUrl, window.location.origin);
        urlObj.searchParams.set("tab", tabName);
        urlObj.searchParams.delete("page");

        if (perPageSelect) {
          urlObj.searchParams.set("per_page_order", perPageSelect.value);
        }

        var activeBtn = document.querySelector(".orders-filter-btn.active");
        if (activeBtn) {
          var statusGroup = activeBtn.getAttribute("data-status-group") || "";
          if (statusGroup) {
            urlObj.searchParams.set("status_group", statusGroup);
          }
        }

        applyDateParams(urlObj);

        loadOrders(urlObj.toString());
      });
    }

    // Nhấn "Xoá lọc" ngày
    if (clearDateBtn) {
      clearDateBtn.addEventListener("click", function () {
        if (dateFromInput) {
          dateFromInput.value = "";
        }
        if (dateToInput) {
          dateToInput.value = "";
        }

        var urlObj = new URL(baseUrl, window.location.origin);
        urlObj.searchParams.set("tab", tabName);
        urlObj.searchParams.delete("page");

        if (perPageSelect) {
          urlObj.searchParams.set("per_page_order", perPageSelect.value);
        }

        var activeBtn = document.querySelector(".orders-filter-btn.active");
        if (activeBtn) {
          var statusGroup = activeBtn.getAttribute("data-status-group") || "";
          if (statusGroup) {
            urlObj.searchParams.set("status_group", statusGroup);
          }
        }

        // Xoá param ngày
        urlObj.searchParams.delete("created_from");
        urlObj.searchParams.delete("created_to");

        loadOrders(urlObj.toString());
      });
    }

    // Phân trang trong bảng đơn hàng => AJAX
    ordersWrapper.addEventListener("click", function (e) {
      var link = e.target.closest(".pagination a");
      if (!link) {
        return;
      }
      e.preventDefault();
      loadOrders(link.href);
    });
  });
})();
