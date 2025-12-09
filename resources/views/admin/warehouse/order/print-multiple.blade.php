<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <title>In nhiều đơn hàng</title>

  <style>
    @page {
      size: A4;
      margin: 10mm;
    }

    * {
      box-sizing: border-box;
      font-family: DejaVu Sans, Arial, sans-serif;
    }

    body {
      margin: 0;
      padding: 0;
      font-size: 12px;
      color: #222;
      background: #f3f4f6;
    }

    .invoice-wrapper {
      max-width: 900px;
      margin: 24px auto 16px auto;
      padding: 16mm 14mm;
      background: #ffffff;
      border-radius: 6px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      border-bottom: 1px solid #e5e7eb;
      padding-bottom: 10px;
      margin-bottom: 12px;
    }

    .invoice-header-left {
      text-transform: uppercase;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .invoice-header-sub {
      font-size: 12px;
      color: #6b7280;
      margin-top: 4px;
    }

    .invoice-header-right {
      text-align: right;
      font-size: 12px;
    }

    .invoice-header-right .order-code {
      font-weight: 700;
      font-size: 13px;
    }

    h1.invoice-title {
      text-align: center;
      font-size: 18px;
      margin: 6px 0 12px 0;
      text-transform: uppercase;
    }

    .section-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 6px;
      letter-spacing: 0.4px;
    }

    .info-row {
      display: flex;
      gap: 16px;
      margin-bottom: 10px;
    }

    .info-col {
      flex: 1 1 0;
      font-size: 12px;
    }

    .info-col p {
      margin: 2px 0;
    }

    .info-label {
      font-weight: 600;
    }

    .divider {
      border-top: 1px dashed #d1d5db;
      margin: 10px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 12px;
    }

    th,
    td {
      border: 1px solid #d1d5db;
      padding: 4px 6px;
      vertical-align: top;
    }

    thead th {
      background: #f9fafb;
      font-weight: 600;
      text-align: center;
      white-space: nowrap;
    }

    .text-right {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    .product-cell {
      display: flex;
      align-items: flex-start;
      gap: 6px;
    }

    .product-image {
      width: 42px;
      height: 56px;
      border-radius: 4px;
      border: 1px solid #e5e7eb;
      object-fit: cover;
      background: #f3f4f6;
    }

    .product-name {
      font-weight: 600;
      margin-bottom: 2px;
    }

    .product-sub {
      font-size: 11px;
      color: #6b7280;
    }

    tfoot td {
      border-top: 0;
    }

    .totals-row-label {
      text-align: right;
      font-weight: 500;
    }

    .totals-row-value {
      text-align: right;
      white-space: nowrap;
    }

    .totals-row-total {
      font-size: 13px;
      font-weight: 700;
      color: #16a34a;
      border-top: 1px solid #9ca3af !important;
    }

    .footer-note {
      margin-top: 10px;
      font-size: 11px;
      color: #6b7280;
    }

    .signatures {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      font-size: 11px;
    }

    .sign-block {
      width: 30%;
      text-align: center;
    }

    .sign-block p {
      margin: 3px 0;
    }

    .page-break {
      page-break-after: always;
    }

    .no-print {
      margin: 16px auto 24px auto;
      text-align: center;
    }

    .btn-print {
      padding: 8px 20px;
      font-size: 12px;
      border-radius: 999px;
      border: 1px solid #4b5563;
      background: #ffffff;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
      transition:
        background 0.15s ease,
        transform 0.1s ease,
        box-shadow 0.1s ease;
    }

    .btn-print::before {
      content: "🖨";
      font-size: 13px;
      line-height: 1;
    }

    .btn-print:hover {
      background: #f3f4f6;
      transform: translateY(-1px);
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.18);
    }

    .btn-print:active {
      transform: translateY(0);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
    }

    @media print {
      body {
        background: #ffffff;
      }

      .invoice-wrapper {
        margin: 0;
        padding: 10mm 8mm;
        box-shadow: none;
        border-radius: 0;
      }

      .page-break {
        display: block;
      }

      .no-print {
        display: none;
      }
    }
  </style>
</head>

<body>
  <div class="no-print">
    <button type="button" class="btn-print" onclick="window.print()">
      In tất cả đơn hàng
    </button>
  </div>
  @php
  $fmtVnd = fn($n) => number_format((int) $n, 0, ',', '.') . ' đ';
  @endphp

  @foreach($orders as $order)
  @php
  $user = $order->user;
  $shipment = $order->shipment;
  @endphp

  <div class="invoice-wrapper">
    <div class="invoice-header">
      <div class="invoice-header-left">
        CÔNG TY TNHH MTV LTNQ
        <div class="invoice-header-sub">
          Địa chỉ: 38C, đường Trần Vĩnh Kiết, Quận Ninh Kiều, TP.Cần Thơ
        </div>
      </div>
      <div class="invoice-header-right">
        <div>Mã đơn: <span class="order-code"># {{ $order->code }}</span></div>
        <div>
          Ngày đặt:
          {{ optional($order->placed_at ?? $order->created_at)->format('d/m/Y H:i') }}
        </div>
      </div>
    </div>

    <h1 class="invoice-title">Hóa đơn mua hàng</h1>

    <div class="info-row">
      <div class="info-col">
        <div class="section-title">Thông tin khách hàng</div>
        <p>
          <span class="info-label">Khách hàng:</span>
          {{ $user?->name ?? 'Khách hàng' }}
        </p>
        @if($user?->email)
        <p>
          <span class="info-label">Email:</span>
          {{ $user->email }}
        </p>
        @endif
        @if($user?->phone)
        <p>
          <span class="info-label">Điện thoại:</span>
          {{ $user->phone }}
        </p>
        @endif
      </div>

      <div class="info-col">
        <div class="section-title">Thông tin giao hàng</div>
        @if($shipment)
        <p>
          <span class="info-label">Người nhận:</span>
          {{ $shipment->name ?? ($user?->name ?? 'Khách hàng') }}
        </p>
        @if($shipment->phone)
        <p>
          <span class="info-label">Điện thoại:</span>
          {{ $shipment->phone }}
        </p>
        @endif
        <p>
          <span class="info-label">Địa chỉ:</span>
          {{ $shipment->address }}
        </p>
        @else
        <p>Chưa có thông tin giao hàng.</p>
        @endif

        <div class="divider"></div>

        <p>
          <span class="info-label">Phương thức thanh toán:</span>
          {{ strtoupper((string) $order->payment_method) }}
        </p>
        <p>
          <span class="info-label">Phí vận chuyển:</span>
          {{ $fmtVnd($order->shipping_fee_vnd) }}
        </p>
      </div>
    </div>

    <div class="divider"></div>

    <div class="section-title">Danh sách sản phẩm</div>

    <table>
      <thead>
        <tr>
          <th style="width: 32px;">#</th>
          <th>Ảnh</th>
          <th>Sản phẩm</th>
          <th style="width: 60px;">SL</th>
          <th style="width: 90px;">Đơn giá</th>
          <th style="width: 80px;">Giảm</th>
          <th style="width: 100px;">Tạm tính</th>
        </tr>
      </thead>
      <tbody>
        @forelse($order->items as $item)
        @php
        $product = $item->product;
        $productName = $item->product_title_snapshot
        ?? ($product->title ?? 'Sản phẩm');

        $authorNames = $product?->authors?->pluck('name')->implode(', ');
        $categoryNames = $product?->categories?->pluck('name')->implode(', ');
        @endphp
        <tr>
          <td class="text-center">
            {{ $loop->iteration }}
          </td>
          <td class="text-center">
            @if($product?->image)
            <img
              src="{{ asset('storage/products/'.$product->image) }}"
              alt="{{ $productName }}"
              class="product-image">
            @else
            <div
              class="product-image"
              style="display:flex;align-items:center;justify-content:center;font-size:10px;color:#9ca3af;">
              N/A
            </div>
            @endif
          </td>
          <td>
            <div class="product-cell">
              <div>
                <div class="product-name">
                  {{ $productName }}
                </div>

                @if($authorNames)
                <div class="product-sub">
                  Tác giả: {{ $authorNames }}
                </div>
                @endif

                @if($categoryNames)
                <div class="product-sub">
                  Danh mục: {{ $categoryNames }}
                </div>
                @endif
              </div>
            </div>
          </td>
          <td class="text-center">
            {{ $item->quantity }}
          </td>
          <td class="text-right">
            {{ $fmtVnd($item->unit_price_vnd) }}
          </td>
          <td class="text-right">
            {{ $fmtVnd($item->discount_amount_vnd ?? 0) }}
          </td>
          <td class="text-right">
            {{ $fmtVnd($item->total_price_vnd) }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center">
            Không có sản phẩm nào trong đơn hàng này.
          </td>
        </tr>
        @endforelse
      </tbody>

      <tfoot>
        <tr>
          <td colspan="6" class="totals-row-label">
            Tạm tính
          </td>
          <td class="totals-row-value">
            {{ $fmtVnd($order->subtotal_vnd) }}
          </td>
        </tr>
        <tr>
          <td colspan="6" class="totals-row-label">
            Phí vận chuyển
          </td>
          <td class="totals-row-value">
            {{ $fmtVnd($order->shipping_fee_vnd) }}
          </td>
        </tr>
        <tr>
          <td colspan="6" class="totals-row-label">
            Giảm giá
          </td>
          <td class="totals-row-value">
            {{ $fmtVnd($order->discount_vnd) }}
          </td>
        </tr>
        <tr>
          <td colspan="6" class="totals-row-label totals-row-total">
            Tổng cộng
          </td>
          <td class="totals-row-value totals-row-total">
            {{ $fmtVnd($order->grand_total_vnd) }}
          </td>
        </tr>
      </tfoot>
    </table>

    <div class="footer-note">
      Lưu ý: Phiếu này dùng để xác nhận đơn hàng và không có giá trị thay thế hóa đơn VAT.
    </div>

    <div class="signatures">
      <div class="sign-block">
        <p><strong>Người lập phiếu</strong></p>
        <p><em>(Ký, ghi rõ họ tên)</em></p>
      </div>
      <div class="sign-block">
        <p><strong>Thủ kho</strong></p>
        <p><em>(Ký, ghi rõ họ tên)</em></p>
      </div>
      <div class="sign-block">
        <p><strong>Người nhận hàng</strong></p>
        <p><em>(Ký, ghi rõ họ tên)</em></p>
      </div>
    </div>
  </div>

  @if(!$loop->last)
  <div class="page-break"></div>
  @endif
  @endforeach


</body>

</html>