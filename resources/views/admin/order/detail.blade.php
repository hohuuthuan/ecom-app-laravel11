@extends('layouts.admin')

@section('title','Products: Chi tiết đơn hàng')

@section('body_class','order-detail-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item">
      <a href="{{ route('admin.dashboard') }}">Admin</a>
    </li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">
      Chi tiết đơn hàng
    </li>
  </ol>
</nav>

@php
  /** @var \App\Models\Order $order */
  $fmtVnd = fn($n) => number_format((int) $n, 0, ',', '.') . ' VNĐ';

  $paymentStatus = strtolower((string) $order->payment_status);

  $paymentStatusTextMap = [
    'unpaid'   => 'Chưa thanh toán',
    'pending'  => 'Chờ thanh toán',
    'paid'     => 'Đã thanh toán',
    'refunded' => 'Đã hoàn tiền',
  ];

  $paymentStatusClassMap = [
    'unpaid'   => 'text-bg-secondary',
    'pending'  => 'text-bg-warning',
    'paid'     => 'text-bg-success',
    'refunded' => 'text-bg-info',
  ];

  $paymentStatusText  = $paymentStatusTextMap[$paymentStatus]  ?? ucfirst($paymentStatus);
  $paymentStatusClass = $paymentStatusClassMap[$paymentStatus] ?? 'text-bg-secondary';

  $shipment = $order->shipment;
  $user     = $order->user;
@endphp

<div class="card mb-3">
  <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h1 class="h4 mb-1">
        Đơn hàng
        <span id="orderCode" class="copyable" title="Nhấp để sao chép">
          #{{ $order->code }}
        </span>
      </h1>
      <div class="text-muted">
        Tạo lúc:
        <span>{{ optional($order->placed_at ?? $order->created_at)->format('d/m/Y H:i') }}</span>
        •
        Cập nhật:
        <span>{{ optional($order->updated_at)->format('d/m/Y H:i') }}</span>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge rounded-pill badge-status {{ $paymentStatusClass }}" id="statusBadge">
        {{ $paymentStatusText }}
      </span>
      <div class="dropdown no-print">
        <select name="" id="" class="setupSelect2">
          <option value="">{{ strtoupper($order->status) }}</option>
        </select>
        <ul class="dropdown-menu dropdown-menu-end" id="statusMenu">
          <li><button class="dropdown-item" data-status="PENDING">Chờ xử lý</button></li>
          <li><button class="dropdown-item" data-status="PROCESSING">Đang xử lý</button></li>
          <li><button class="dropdown-item" data-status="SHIPPING">Đang giao</button></li>
          <li><button class="dropdown-item" data-status="DONE">Hoàn tất</button></li>
          <li><hr class="dropdown-divider"></li>
          <li><button class="dropdown-item text-danger" data-status="CANCEL">Hủy</button></li>
        </ul>
      </div>
      <span class="vr d-none d-md-block"></span>
      <div class="text-end">
        <div class="mini text-muted">Tổng tiền</div>
        <div class="h5 mb-0" id="grandTotal">
          {{ $fmtVnd($order->grand_total_vnd) }}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 3-column sections -->
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Khách hàng</div>
        <div class="d-flex align-items-start gap-3">
          <div
            class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
            style="width:48px;height:48px">
            <i class="bi bi-person-fill" style="font-size:1.25rem"></i>
          </div>
          <div>
            <div class="fw-semibold">
              {{ $user?->name ?? 'Khách hàng' }}
            </div>
            <div class="text-muted mini">
              @if($user?->username)
                user: {{ $user->username }}
              @elseif($user?->email)
                {{ $user->email }}
              @else
                ID: {{ $order->user_id }}
              @endif
            </div>
            <div class="mt-2">
              @if($user?->email)
                <div class="mini">
                  <i class="bi bi-envelope"></i>
                  <a href="mailto:{{ $user->email }}">
                    {{ $user->email }}
                  </a>
                </div>
              @endif
              @if($user?->phone)
                <div class="mini">
                  <i class="bi bi-telephone"></i>
                  {{ $user->phone }}
                </div>
              @endif
            </div>
          </div>
        </div>
        <hr>
        <div class="section-title mb-2">Thanh toán</div>
        <div class="mini">
          Phương thức:
          {{ strtoupper($order->payment_method) }}
        </div>
        <div class="mini">
          Mã giao dịch:
          <span class="copyable" id="txnId">
            {{-- hiện tại COD chưa có payment record, để trống hoặc TODO --}}
            {{ $order->payment_method === 'cod' ? 'COD' : '—' }}
          </span>
        </div>
        <div class="mini">
          Trạng thái:
          <span class="badge {{ $paymentStatusClass }}">
            {{ $paymentStatusText }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Giao hàng</div>
        <div class="mini text-muted mb-2">Địa chỉ nhận</div>

        @if($shipment)
          <div>{{ $shipment->name }}</div>
          <div>{{ $shipment->address }}</div>
          @if($shipment->phone)
            <div class="mini mt-2">
              <i class="bi bi-telephone"></i>
              {{ $shipment->phone }}
            </div>
          @endif
        @else
          <div>Chưa có thông tin giao hàng</div>
        @endif

        <hr>
        <div class="mini">
          Phí vận chuyển:
          {{ $fmtVnd($order->shipping_fee_vnd) }}
        </div>
        <div class="mini">
          Đơn vị VC:
          {{ $shipment?->courier_name ?? '—' }}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Items -->
<div class="card mt-3">
  <div class="card-body">
    <div class="section-title mb-3">Sản phẩm</div>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>TÊN SẢN PHẨM</th>
            <th>MÃ ISBN13</th>
            <th class="text-center">SỐ LƯỢNG</th>
            <th class="text-end">ĐƠN GIÁ</th>
            <th class="text-end">GIẢM</th>
            <th class="text-end">TẠM TÍNH</th>
          </tr>
        </thead>
        <tbody id="itemBody">
          @forelse($order->items as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <div class="fw-semibold">
                  {{ $item->product_title_snapshot ?? $item->product->title ?? 'Sản phẩm' }}
                </div>
                <div class="text-muted mini">
                  ID: {{ $item->product_id }}
                </div>
              </td>
              <td>
                {{ $item->isbn13_snapshot ?? $item->product_id }}
              </td>
              <td class="text-center">
                {{ $item->quantity }}
              </td>
              <td class="text-end">
                {{ $fmtVnd($item->unit_price_vnd) }}
              </td>
              <td class="text-end">
                {{ $fmtVnd($item->discount_amount_vnd ?? 0) }}
              </td>
              <td class="text-end">
                {{ $fmtVnd($item->total_price_vnd) }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted mini">
                Không có sản phẩm nào trong đơn hàng này.
              </td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6" class="text-end">Tạm tính</td>
            <td class="text-end">
              {{ $fmtVnd($order->subtotal_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Phí vận chuyển</td>
            <td class="text-end">
              {{ $fmtVnd($order->shipping_fee_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Giảm giá</td>
            <td class="text-end">
              {{ $fmtVnd($order->discount_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="6" class="text-end">Tổng cộng</td>
            <td class="text-end h5 mb-0 text-success">
              {{ $fmtVnd($order->grand_total_vnd) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="d-flex justify-content-end gap-2 no-print">
      <button class="btn btn-outline-secondary">
        <i class="bi bi-filetype-csv"></i> Xuất CSV
      </button>
      <button class="btn btn-outline-secondary">
        <i class="bi bi-receipt"></i> Hóa đơn
      </button>
      <button class="btn btn-outline-danger">
        <i class="bi bi-box-arrow-in-left"></i> Hoàn/Trả
      </button>
    </div>
  </div>
</div>

<!-- Timeline + Internal notes -->
<div class="row g-3 mt-1">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Dòng thời gian</div>

        @php
          $timeline = [];

          if ($order->placed_at) {
            $timeline[] = [
              'label' => 'Đã tạo đơn',
              'desc'  => $order->placed_at->format('d/m/Y H:i') . ' • ' . strtoupper($order->payment_method),
            ];
          }

          if ($shipment && $shipment->picked_at) {
            $timeline[] = [
              'label' => 'Đã lấy hàng',
              'desc'  => $shipment->picked_at->format('d/m/Y H:i'),
            ];
          }

          if ($shipment && $shipment->shipped_at) {
            $timeline[] = [
              'label' => 'Đang vận chuyển',
              'desc'  => $shipment->shipped_at->format('d/m/Y H:i'),
            ];
          }

          if ($shipment && $shipment->delivered_at) {
            $timeline[] = [
              'label' => 'Giao thành công',
              'desc'  => $shipment->delivered_at->format('d/m/Y H:i'),
            ];
          }

          if ($order->cancelled_at) {
            $timeline[] = [
              'label' => 'Đã hủy đơn',
              'desc'  => $order->cancelled_at->format('d/m/Y H:i'),
            ];
          }
        @endphp

        @if(count($timeline) > 0)
          <div class="timeline">
            @foreach($timeline as $row)
              <div class="timeline-item">
                <div class="fw-semibold">{{ $row['label'] }}</div>
                <div class="text-muted mini">{{ $row['desc'] }}</div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-muted mini">
            Chưa có log thời gian cho đơn hàng này.
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-body">
        <div class="section-title mb-3">Ghi chú nội bộ</div>
        <div class="mb-2 mini text-muted">Chỉ hiển thị cho admin</div>

        <ul class="list-group mb-3" id="noteList">
          @if($order->buyer_note)
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="me-2">
                {{ $order->buyer_note }}
                <div class="mini text-muted">
                  {{ optional($order->placed_at ?? $order->created_at)->format('d/m/Y H:i') }} • Khách hàng
                </div>
              </div>
              <button class="btn btn-sm btn-outline-danger no-print" disabled>
                <i class="bi bi-trash"></i>
              </button>
            </li>
          @endif
        </ul>

        @if(!$order->buyer_note)
          <div class="mini text-muted mb-3">
            Chưa có ghi chú từ khách hàng.
          </div>
        @endif

        <div class="input-group">
          <input
            type="text"
            class="form-control"
            placeholder="Thêm ghi chú (TODO API)..."
            id="noteInput"
            disabled>
          <button class="btn btn-primary" id="noteAdd" disabled>
            <i class="bi bi-plus-lg"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@include('partials.ui.confirm-modal')
@endsection