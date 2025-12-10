@extends('layouts.admin')

@section('title','Orders: Đơn cần hoàn tiền')

@section('body_class','order-refund-issue-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="<?php echo route('admin.dashboard'); ?>">Admin</a>
    </li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">
      Đơn cần hoàn tiền
    </li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách đơn cần hoàn tiền</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        <?php $pp = (int) request('per_page_issue', 10); ?>
        <select
          class="form-select form-select-sm w-auto setupSelect2"
          name="per_page_issue"
          onchange="this.form.submit()">
          <option value="10" <?php echo $pp === 10 ? 'selected' : ''; ?>>10</option>
          <option value="20" <?php echo $pp === 20 ? 'selected' : ''; ?>>20</option>
          <option value="50" <?php echo $pp === 50 ? 'selected' : ''; ?>>50</option>
        </select>

        <input type="hidden" name="keyword" value="<?php echo request('keyword'); ?>">
        <input type="hidden" name="refund_status" value="<?php echo request('refund_status'); ?>">
        <input type="hidden" name="issued_from" value="<?php echo request('issued_from'); ?>">
        <input type="hidden" name="issued_to" value="<?php echo request('issued_to'); ?>">
      </form>
    </div>

    <div class="card-body">
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
                placeholder="Tìm theo mã đơn / SDT / email / tên khách"
                value="<?php echo request('keyword'); ?>">
            </div>
            <div class="col-md-2">
              <label class="d-block mb-1">&nbsp;</label>
              <button type="submit" class="btn-admin">
                <i class="fa fa-search me-1"></i>
                Tìm kiếm
              </button>
            </div>
          </div>

          <div class="row">
            <div class="col-md-2">
              <label
                for="refund_status"
                class="form-label mb-1 label-filter-admin-product">
                Trạng thái hoàn tiền
              </label>
              <?php $rs = request('refund_status'); ?>
              <select
                id="refund_status"
                name="refund_status"
                class="form-select setupSelect2">
                <option value="">-- Tất cả --</option>
                <option value="NEED_REFUND" <?php echo $rs === 'NEED_REFUND' ? 'selected' : ''; ?>>
                  Chưa hoàn tiền
                </option>
                <option value="REFUNDED" <?php echo $rs === 'REFUNDED' ? 'selected' : ''; ?>>
                  Đã hoàn tiền
                </option>
              </select>
            </div>

            <div class="col-md-2">
              <label
                for="issued_from"
                class="form-label mb-1 label-filter-admin-product">
                Từ ngày
              </label>
              <input
                id="issued_from"
                type="date"
                name="issued_from"
                class="form-control"
                value="<?php echo request('issued_from'); ?>">
            </div>

            <div class="col-md-2">
              <label
                for="issued_to"
                class="form-label mb-1 label-filter-admin-product">
                Đến ngày
              </label>
              <input
                id="issued_to"
                type="date"
                name="issued_to"
                class="form-control"
                value="<?php echo request('issued_to'); ?>">
            </div>
          </div>
        </div>
      </form>

      <div class="table-responsive">
        <table
          id="issueTable"
          class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="th-order-table STT_Width">#</th>
              <th class="th-order-table th-order-code">MÃ ĐƠN HÀNG</th>
              <th class="th-order-table infoWidth">KHÁCH HÀNG \ SĐT</th>
              <th class="th-order-table th-order-method">PHƯƠNG THỨC</th>
              <th class="th-order-table reasonWidth">LÝ DO</th>
              <th class="th-order-table statusWidth">TIỀN CẦN HOÀN</th>
              <th class="th-order-table statusWidth">TRẠNG THÁI HOÀN TIỀN</th>
              <th class="th-order-table actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($issues->count() > 0): ?>
              <?php foreach ($issues as $idx => $issue): ?>
                <?php
                $firstItem = $issues->firstItem();
                $rowIndex  = ($firstItem !== null ? $firstItem : 0) + $idx;

                $order    = $issue->order;
                $shipment = $order ? $order->shipment : null;
                $user     = $order ? $order->user : null;

                $orderCode = $order ? ($order->code ?: ('ORD-' . $order->id)) : '—';
                $customer  = $shipment->name ?? ($user->name ?? '—');
                $phone     = $shipment->phone ?? ($user->phone ?? '—');

                $paymentMethod = strtoupper($issue->order_payment_method ?? ($order->payment_method ?? '—'));

                $issueType  = strtoupper($issue->issue_type ?? '');
                $issuedAt   = $issue->issued_at ? $issue->issued_at->format('d/m/Y H:i') : '';
                $reasonText = trim((string) ($issue->reason ?? ''));

                $refundAmount = (int) ($issue->refund_amount_vnd ?? 0);
                $isRefunded   = (bool) $issue->is_refunded;
                ?>
                <tr>
                  <td><?php echo $rowIndex; ?></td>

                  <td>
                    <?php if ($order): ?>
                      <a href="<?php echo route('admin.order.detail', $order->id); ?>">
                        <?php echo $orderCode; ?>
                      </a>
                    <?php else: ?>
                      <?php echo $orderCode; ?>
                    <?php endif; ?>
                  </td>

                  <td>
                    <div>
                      <?php echo $customer; ?>
                      \
                      <?php echo $phone; ?>
                    </div>
                  </td>

                  <td><?php echo $paymentMethod ?: '—'; ?></td>

                  <td>
                    <div>
                      <?php
                      if ($reasonText !== '') {
                        echo e($reasonText);
                      } else {
                        if ($issueType === 'DELIVERY_FAILED') {
                          echo 'Giao hàng thất bại';
                        } elseif ($issueType === 'RETURNED') {
                          echo 'Hoàn / trả hàng';
                        } else {
                          echo 'Không xác định';
                        }
                      }
                      ?>
                    </div>
                    <?php if ($issuedAt !== ''): ?>
                      <div class="mini text-muted">
                        Thời gian: <?php echo $issuedAt; ?>
                      </div>
                    <?php endif; ?>
                  </td>

                  <td class="text-end text-success fw-semibold">
                    <?php echo number_format($refundAmount, 0, ',', '.'); ?> đ
                  </td>

                  <td>
                    <?php if ($isRefunded): ?>
                      <span class="badge rounded-pill badge-status badge-status--success">
                        Đã hoàn tiền
                      </span>
                    <?php else: ?>
                      <span class="badge rounded-pill badge-status badge-status--warning">
                        Chưa hoàn tiền
                      </span>
                    <?php endif; ?>
                  </td>

                  <td class="text-center">
                    <?php if ($order): ?>
                      
                    <?php endif; ?>

                    <?php if (!$isRefunded && $refundAmount > 0): ?>
                      <form
                        method="POST"
                        action="<?php echo route('admin.order.issues.markRefunded', $issue->id); ?>"
                        style="display:inline-block;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <a
                        href="<?php echo route('admin.order.detail', $order->id); ?>"
                        class="me-2">
                        <i class="fa fa-eye icon-eye-view-order-detail"></i>
                      </a>
                        <button
                          type="submit"
                          class="btn btn-sm btn-admin">
                          Đánh dấu đã hoàn
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted">
                  Không có đơn cần hoàn tiền.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <?php echo $issues->appends(request()->except('page'))->links('pagination::bootstrap-5'); ?>
      </div>
    </div>
  </div>
</div>

@include('partials.ui.confirm-modal')
@endsection
