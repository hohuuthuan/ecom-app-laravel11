@extends('layouts.admin')

@section('title','Orders: Danh sách đơn hàng')

@section('body_class','order-index-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="<?php echo route('admin.dashboard'); ?>">Admin</a>
    </li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">
      Đơn hàng
    </li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách đơn hàng</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        <?php $pp = (int) request('per_page_order', 10); ?>
        <select
          class="form-select form-select-sm w-auto setupSelect2"
          name="per_page_order"
          onchange="this.form.submit()">
          <option value="10" <?php echo $pp === 10 ? 'selected' : ''; ?>>10</option>
          <option value="20" <?php echo $pp === 20 ? 'selected' : ''; ?>>20</option>
          <option value="50" <?php echo $pp === 50 ? 'selected' : ''; ?>>50</option>
        </select>

        <input type="hidden" name="keyword" value="<?php echo request('keyword'); ?>">
        <input type="hidden" name="payment_method" value="<?php echo request('payment_method'); ?>">
        <input type="hidden" name="payment_status" value="<?php echo request('payment_status'); ?>">
        <input type="hidden" name="status" value="<?php echo request('status'); ?>">
        <input type="hidden" name="created_from" value="<?php echo request('created_from'); ?>">
        <input type="hidden" name="created_to" value="<?php echo request('created_to'); ?>">
      </form>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form method="GET" class="row g-2 mb-3 filter-form">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-6 searchProduct">
              <label
                for="keyword"
                class="form-label mb-1 label-filter-admin-product">
                Tìm kiếm
              </label>
              <input
                id="keyword"
                type="text"
                name="keyword"
                class="form-control"
                placeholder="Tìm theo mã / SDT / email / tên"
                value="<?php echo request('keyword'); ?>">
            </div>

            <div class="col-md-4">
              <label class="d-block mb-1">&nbsp;</label>
              <button type="submit" class="btn-admin">
                <i class="fa fa-search me-1"></i>
                Tìm kiếm
              </button>
              <a href="{{ route('admin.order.index') }}" class="btn btn-outline-secondary btn-submit-filter-admin-order">
                <i class="fa fa-eraser me-1"></i> Xóa lọc
              </a>
            </div>

            <?php
            // Đếm số sự cố cần hoàn tiền (chỉ MOMO, VNPAY – bỏ COD)
            $needRefundCount = 0;
            if (isset($orders) && $orders->count() > 0) {
              foreach ($orders as $order) {
                $unresolvedIssues = (int) ($order->unresolved_delivery_issues_count ?? 0);
                $pm = strtoupper($order->payment_method ?? '');
                if ($unresolvedIssues > 0 && in_array($pm, ['MOMO', 'VNPAY'], true)) {
                  $needRefundCount += $unresolvedIssues;
                }
              }
            }
            ?>

            <div class="col-md-2">
              <a
                href="<?php echo route('admin.order.issues.list'); ?>"
                class="text-decoration-none">
                <div class="card shadow-sm h-100">
                  <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <div class="fw-semibold mini text-muted">
                          Đơn không thành công
                        </div>
                        <div class="fw-semibold">
                          Cần hoàn tiền
                        </div>
                        <div class="h4 mb-0">
                          <?php echo number_format($needRefundCount, 0, ',', '.'); ?> đơn
                        </div>
                      </div>
                      <div class="ms-2">
                        <i class="fa fa-eye icon-eye-view-order-detail"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>

          <div class="row searchProduct">
            <div class="col-md-2">
              <label
                for="payment_method"
                class="form-label mb-1 label-filter-admin-product">
                Phương thức
              </label>
              <select
                id="payment_method"
                name="payment_method"
                class="form-select setupSelect2">
                <?php $pm = request('payment_method'); ?>
                <option value="">-- Tất cả phương thức --</option>
                <option value="COD" <?php echo $pm === 'COD'  ? 'selected' : ''; ?>>COD</option>
                <option value="MOMO" <?php echo $pm === 'MOMO' ? 'selected' : ''; ?>>MOMO</option>
                <option value="VNPAY" <?php echo $pm === 'VNPAY' ? 'selected' : ''; ?>>VNPAY</option>
              </select>
            </div>

            <div class="col-md-2">
              <label
                for="payment_status"
                class="form-label mb-1 label-filter-admin-product">
                TT thanh toán
              </label>
              <select
                id="payment_status"
                name="payment_status"
                class="form-select setupSelect2">
                <?php $ps = request('payment_status'); ?>
                <option value="">-- Tất cả trạng thái --</option>
                <option value="UNPAID" <?php echo $ps === 'UNPAID' ? 'selected' : ''; ?>>Chưa thanh toán</option>
                <option value="PAID" <?php echo $ps === 'PAID'   ? 'selected' : ''; ?>>Đã thanh toán</option>
              </select>
            </div>

            <div class="col-md-2">
              <label
                for="status"
                class="form-label mb-1 label-filter-admin-product">
                Trạng thái đơn
              </label>
              <select
                id="status"
                name="status"
                class="form-select setupSelect2">
                <?php $st = request('status'); ?>
                <option value="">-- Tất cả trạng thái --</option>

                <option value="PENDING" <?php echo $st === 'PENDING'         ? 'selected' : ''; ?>>Đơn mới</option>
                <option value="PROCESSING" <?php echo $st === 'PROCESSING'      ? 'selected' : ''; ?>>Đã tiếp nhận / chuyển kho</option>
                <option value="PICKING" <?php echo $st === 'PICKING'         ? 'selected' : ''; ?>>Đang lấy hàng</option>
                <option value="SHIPPING" <?php echo $st === 'SHIPPING'        ? 'selected' : ''; ?>>Đang giao / đã giao cho ĐVVC</option>
                <option value="COMPLETED" <?php echo $st === 'COMPLETED'       ? 'selected' : ''; ?>>Hoàn tất</option>
                <option value="CANCELLED" <?php echo $st === 'CANCELLED'       ? 'selected' : ''; ?>>Đã huỷ</option>
                <option value="DELIVERY_FAILED" <?php echo $st === 'DELIVERY_FAILED' ? 'selected' : ''; ?>>Giao thất bại</option>
                <option value="RETURNED" <?php echo $st === 'RETURNED'        ? 'selected' : ''; ?>>Hoàn / trả hàng</option>
              </select>
            </div>

            <div class="col-md-2">
              <label
                for="created_from"
                class="form-label mb-1 label-filter-admin-product">
                Từ ngày
              </label>
              <input
                id="created_from"
                type="date"
                name="created_from"
                class="form-control"
                value="<?php echo request('created_from'); ?>">
            </div>

            <div class="col-md-2">
              <label
                for="created_to"
                class="form-label mb-1 label-filter-admin-product">
                Đến ngày
              </label>
              <input
                id="created_to"
                type="date"
                name="created_to"
                class="form-control"
                value="<?php echo request('created_to'); ?>">
            </div>
          </div>
        </div>
      </form>

      {{-- Bulk update status --}}
      <form
        id="orderBulkForm"
        method="POST"
        action="<?php echo route('admin.order.bulkChangeStatus'); ?>"
        class="row g-2 align-items-end mb-3">
        @csrf
        @method('PATCH')

        <div class="col-md-2">
          <label
            for="bulk_order_status"
            class="form-label mb-1 label-filter-admin-product">
            Cập nhật nhiều đơn hàng
          </label>
          <select
            id="bulk_order_status"
            name="status"
            class="form-select setupSelect2"
            required>
            <option value="" selected disabled>-- Chọn trạng thái --</option>
            <option value="PROCESSING">Tiếp nhận đơn, chuyển đơn sang  đơn vị kho</option>
            <option value="CANCELLED">Huỷ đơn</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="d-block mb-1">&nbsp;</label>
          <button
            id="btnOrderBulkApply"
            type="button"
            class="btn-admin"
            disabled>
            Áp dụng
          </button>
        </div>

        <div id="orderBulkIds"></div>
      </form>

      <div class="table-responsive">
        <table
          id="orderTable"
          class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="th-order-table checkAllWidth">
                <input type="checkbox" id="order_check_all">
              </th>
              <th class="th-order-table STT_Width">#</th>
              <th class="th-order-table th-order-code">MÃ ĐƠN HÀNG</th>
              <th class="th-order-table">TÊN KHÁCH HÀNG \ SĐT</th>
              <th class="th-order-table th-order-method">PHƯƠNG THỨC</th>
              <th class="th-order-table th-date-order">NGÀY TẠO</th>
              <th class="th-order-table statusWidth">TRẠNG THÁI THANH TOÁN</th>
              <th class="th-order-table statusWidth">TRẠNG THÁI ĐƠN HÀNG</th>
              <th class="th-order-table incidentWidth text-center">SỰ CỐ \ HOÀN TIỀN</th>
              <th class="th-order-table actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($orders->count() > 0): ?>
              <?php foreach ($orders as $idx => $order): ?>
                <tr>
                  <td>
                    <input
                      type="checkbox"
                      class="order-row-checkbox"
                      value="<?php echo $order->id; ?>">
                  </td>

                  <td>
                    <?php
                    $firstItem = $orders->firstItem();
                    echo ($firstItem !== null ? $firstItem : 0) + $idx;
                    ?>
                  </td>

                  <td>
                    <a href="<?php echo route('admin.order.detail', $order->id); ?>">
                      <?php echo $order->code ?: ('ORD-' . $order->id); ?>
                    </a>
                  </td>

                  <td>
                    <div>
                      <?php echo $order->shipment->name ?? '—'; ?>
                      \
                      <?php
                      $phoneOrEmail = $order->shipment->phone ?? $order->shipment->email ?? '—';
                      echo $phoneOrEmail;
                      ?>
                    </div>
                  </td>

                  <td><?php echo strtoupper($order->payment_method ?? '—'); ?></td>

                  <td>
                    <?php echo $order->placed_at ? $order->placed_at->format('d/m/Y h:i A') : ''; ?>
                  </td>

                  {{-- TRẠNG THÁI THANH TOÁN --}}
                  <td>
                    <?php
                    $payStatus = strtoupper($order->payment_status ?? '');
                    if ($payStatus === 'PAID'): ?>
                      <span class="badge rounded-pill badge-status badge-status--success">
                        Đã thanh toán
                      </span>
                    <?php elseif ($payStatus === 'UNPAID'): ?>
                      <span class="badge bg-secondary">
                        Chưa thanh toán
                      </span>
                    <?php else: ?>
                      <span class="badge rounded-pill badge-status badge-status--primary">
                        Không xác định
                      </span>
                    <?php endif; ?>
                  </td>

                  {{-- TRẠNG THÁI ĐƠN HÀNG --}}
                  <td>
                    <?php
                    $status = strtolower($order->status ?? '');
                    if ($status === 'pending'): ?>
                      <span class="badge rounded-pill badge-status badge-status--warning">
                        Chờ xử lý
                      </span>
                    <?php elseif (in_array($status, ['processing', 'confirmed'], true)): ?>
                      <span class="badge rounded-pill badge-status badge-status--warning">
                        Đã tiếp nhận / chuyển kho
                      </span>
                    <?php elseif ($status === 'picking'): ?>
                      <span class="badge rounded-pill badge-status badge-status--warning">
                        Đang chuẩn bị hàng
                      </span>
                    <?php elseif (in_array($status, ['shipping', 'shipped'], true)): ?>
                      <span class="badge rounded-pill badge-status badge-status--warning">
                        Đang giao / đã giao cho ĐVVC
                      </span>
                    <?php elseif (in_array($status, ['completed', 'delivered'], true)): ?>
                      <span class="badge rounded-pill badge-status badge-status--success">
                        Hoàn tất
                      </span>
                    <?php elseif (in_array($status, ['cancelled', 'delivery_failed'], true)): ?>
                      <span class="badge rounded-pill badge-status badge-status--danger">
                        Đã huỷ / giao thất bại
                      </span>
                    <?php elseif ($status === 'returned'): ?>
                      <span class="badge rounded-pill badge-status badge-status--warning">
                        Hoàn / trả hàng
                      </span>
                    <?php else: ?>
                      <span class="badge rounded-pill badge-status badge-status--primary">
                        Không xác định
                      </span>
                    <?php endif; ?>
                  </td>

                  {{-- SỰ CỐ / HOÀN TIỀN --}}
                  <td class="text-center">
                    <?php
                    $totalIssues      = (int) ($order->delivery_issues_count ?? 0);
                    $unresolvedIssues = (int) ($order->unresolved_delivery_issues_count ?? 0);
                    if ($unresolvedIssues > 0):
                    ?>
                      <a
                        href="<?php echo route('admin.order.detail', $order->id); ?>"
                        class="badge rounded-pill badge-status badge-status--danger text-decoration-none">
                        Cần xử lý (<?php echo $unresolvedIssues; ?>)
                      </a>
                    <?php elseif ($totalIssues > 0): ?>
                      <a
                        href="<?php echo route('admin.order.detail', $order->id); ?>"
                        class="badge rounded-pill badge-status badge-status--success text-decoration-none">
                        Đã xử lý (<?php echo $totalIssues; ?>)
                      </a>
                    <?php else: ?>
                      <span class="text-muted mini">—</span>
                    <?php endif; ?>
                  </td>

                  <td class="text-center">
                    <a href="<?php echo route('admin.order.detail', $order->id); ?>">
                      <i class="fa fa-eye icon-eye-view-order-detail"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="text-center text-muted">
                  Không có đơn hàng.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <?php echo $orders->appends(request()->except('page'))->links('pagination::bootstrap-5'); ?>
      </div>
    </div>
  </div>
</div>

@include('partials.ui.confirm-modal')
@endsection

@push('scripts')
<script>
  (function () {
    'use strict';

    function qs(selector, root) {
      return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
      return Array.from((root || document).querySelectorAll(selector));
    }

    function getRowCheckboxes() {
      return qsa('#orderTable tbody .order-row-checkbox');
    }

    function getCheckedIds() {
      return qsa('#orderTable tbody .order-row-checkbox:checked').map(function (cb) {
        return cb.value;
      });
    }

    function makeHiddenInputs(container, name, values) {
      container.innerHTML = '';
      values.forEach(function (v) {
        var i = document.createElement('input');
        i.type = 'hidden';
        i.name = name;
        i.value = v;
        container.appendChild(i);
      });
    }

    function setRowState(checkbox) {
      var row = checkbox.closest('tr');
      if (!row) {
        return;
      }

      if (checkbox.checked) {
        row.classList.add('row-checked');
        return;
      }

      row.classList.remove('row-checked');
    }

    function syncAllRowStates(rows) {
      rows.forEach(function (cb) {
        setRowState(cb);
      });
    }

    function updateMasterState(master, rows) {
      var checked = rows.filter(function (cb) {
        return cb.checked;
      }).length;

      if (checked === 0) {
        master.checked = false;
        master.indeterminate = false;
        return;
      }

      if (checked === rows.length) {
        master.checked = true;
        master.indeterminate = false;
        return;
      }

      master.checked = false;
      master.indeterminate = true;
    }

    function updateBulkButton() {
      var btn = qs('#btnOrderBulkApply');
      var select = qs('#bulk_order_status');
      if (!btn || !select) {
        return;
      }

      btn.disabled = getCheckedIds().length === 0 || !select.value;
    }

    function normalizeConfirmText(html) {
      return String(html || '').replace(/<[^>]*>/g, '').trim();
    }

    function confirmUI(title, messageHtml) {
      if (typeof window.UIConfirm === 'function') {
        return window.UIConfirm({ title: title, message: messageHtml });
      }
      return Promise.resolve(confirm(normalizeConfirmText(messageHtml)));
    }

    function submitBulk() {
      var form = qs('#orderBulkForm');
      var idsBox = qs('#orderBulkIds');
      var select = qs('#bulk_order_status');

      if (!form || !idsBox || !select) {
        return;
      }

      var ids = getCheckedIds();
      if (!ids.length) {
        return;
      }

      makeHiddenInputs(idsBox, 'ids[]', ids);
      form.submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
      var master = qs('#order_check_all');
      var table = qs('#orderTable');
      var rows = getRowCheckboxes();

      if (master) {
        master.addEventListener('change', function () {
          rows = getRowCheckboxes();
          rows.forEach(function (cb) {
            cb.checked = master.checked;
          });
          master.indeterminate = false;
          syncAllRowStates(rows);
          updateBulkButton();
        });
      }

      table?.addEventListener('change', function (e) {
        if (!e.target || !e.target.classList || !e.target.classList.contains('order-row-checkbox')) {
          return;
        }

        rows = getRowCheckboxes();
        if (master) {
          updateMasterState(master, rows);
        }
        setRowState(e.target);
        updateBulkButton();
      });

      qs('#bulk_order_status')?.addEventListener('change', function () {
        updateBulkButton();
      });

      qs('#btnOrderBulkApply')?.addEventListener('click', async function () {
        var ids = getCheckedIds();
        if (!ids.length) {
          return;
        }

        var select = qs('#bulk_order_status');
        if (!select || !select.value) {
          alert('Vui lòng chọn trạng thái muốn cập nhật.');
          return;
        }

        var status = String(select.value || '').toUpperCase();
        var label = status === 'PROCESSING' ? 'Tiếp nhận đơn' : 'Huỷ đơn';

        var ok = await confirmUI(
          'Xác nhận cập nhật',
          'Bạn sắp cập nhật <b>' + label + '</b> cho <b>' + ids.length + '</b> đơn hàng.'
        );

        if (!ok) {
          return;
        }

        submitBulk();
      });

      syncAllRowStates(rows);
      updateBulkButton();
    });
  })();
</script>
@endpush
