<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <title>Đặt hàng thành công #{{ $order->code ?? '' }}</title>

  <style>
    * {
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    }

    body {
      margin: 0;
      padding: 0;
      font-size: 12px;
      color: #222;
      background: #f3f4f6;
    }

    .email-wrapper {
      width: 100%;
      padding: 16px 0;
    }

    .invoice-wrapper {
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
      padding: 16px 14px 20px 14px;
      background: #ffffff;
      border-radius: 6px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      border-bottom: 1px solid #e5e7eb;
      margin-bottom: 14px;
    }

    .header-left {
      padding: 0 0 8px 0;
      text-transform: uppercase;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.5px;
      vertical-align: top;
    }

    .header-sub {
      font-size: 12px;
      color: #6b7280;
      margin-top: 4px;
    }

    .header-right {
      padding: 0 0 8px 0;
      text-align: right;
      font-size: 12px;
      vertical-align: top;
      white-space: nowrap;
    }

    .header-right .order-code {
      font-weight: 700;
      font-size: 13px;
    }

    .invoice-title {
      text-align: center;
      font-size: 18px;
      text-transform: uppercase;
      padding: 6px 0 10px 0;
    }

    .section-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 6px;
      letter-spacing: 0.4px;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }

    .info-table td {
      vertical-align: top;
      padding: 0;
      padding-right: 16px;
      font-size: 12px;
    }

    .info-table td:last-child {
      padding-right: 0;
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

    table.products-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 12px;
    }

    table.products-table th,
    table.products-table td {
      border: 1px solid #d1d5db;
      padding: 4px 6px;
      vertical-align: top;
    }

    table.products-table thead th {
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
      display: block;
    }

    .product-name {
      font-weight: 600;
      margin-bottom: 2px;
    }

    .product-sub {
      font-size: 11px;
      color: #6b7280;
    }

    table.products-table tfoot td {
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

    .signatures-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
      font-size: 11px;
    }

    .signatures-table td {
      text-align: center;
      padding-top: 4px;
    }

    .signatures-table strong {
      display: block;
      margin-bottom: 3px;
    }

    @media only screen and (max-width: 640px) {
      .invoice-wrapper {
        padding: 12px 10px 16px 10px;
        border-radius: 0;
      }

      .header-right {
        padding-top: 6px;
        display: block;
      }

      .header-table tr:first-child td {
        display: block;
        width: 100%;
      }

      .header-right {
        text-align: left;
        white-space: normal;
      }

      .info-table td {
        display: block;
        width: 100%;
        padding-right: 0;
        margin-bottom: 8px;
      }

      .signatures-table td {
        display: block;
        width: 100%;
        padding-bottom: 10px;
      }
    }
  </style>
</head>

<body>
@php
    /** @var \App\Models\Order $order */
    $fmtVnd = fn($n) => number_format((int) $n, 0, ',', '.') . ' đ';

    $user = $order->user;
    $shipment = $order->shipment;

    $customerName = $shipment?->name
        ?? $user?->name
        ?? 'Khách hàng';
@endphp

  <div class="email-wrapper">
    <div class="invoice-wrapper">
      {{-- HEADER --}}
      <table class="header-table" role="presentation" cellpadding="0" cellspacing="0">
        <tr>
          <td class="header-left">
            CÔNG TY TNHH MTV LTNQ
            <div class="header-sub">
              Địa chỉ: 38C, đường Trần Vĩnh Kiết, Quận Ninh Kiều, TP.Cần Thơ
            </div>
          </td>
          <td class="header-right">
            <div>Mã đơn: <span class="order-code"># {{ $order->code }}</span></div>
            <div>
              Ngày đặt:
              {{ optional($order->placed_at ?? $order->created_at)->format('d/m/Y H:i') }}
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="invoice-title">
            Hóa đơn mua hàng
          </td>
        </tr>
      </table>

      {{-- THÔNG TIN KHÁCH HÀNG & GIAO HÀNG --}}
      <table class="info-table" role="presentation" cellpadding="0" cellspacing="0">
        <tr>
          <td class="info-col">
            <div class="section-title">Thông tin khách hàng</div>
            <p>
              <span class="info-label">Khách hàng:</span>
              {{ $customerName }}
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
          </td>

          <td class="info-col">
            <div class="section-title">Thông tin giao hàng</div>
            @if($shipment)
              <p>
                <span class="info-label">Người nhận:</span>
                {{ $shipment->name ?? $customerName }}
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
          </td>
        </tr>
      </table>

      <div class="divider"></div>

      {{-- DANH SÁCH SẢN PHẨM --}}
      <div class="section-title">Danh sách sản phẩm</div>

      <table class="products-table">
        <thead>
          <tr>
            <th style="width: 32px;">#</th>
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
            <td>
              <div class="product-cell">
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
            <td colspan="6" class="text-center">
              Không có sản phẩm nào trong đơn hàng này.
            </td>
          </tr>
        @endforelse
        </tbody>

        <tfoot>
          <tr>
            <td colspan="5" class="totals-row-label">
              Tạm tính
            </td>
            <td class="totals-row-value">
              {{ $fmtVnd($order->subtotal_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="5" class="totals-row-label">
              Phí vận chuyển
            </td>
            <td class="totals-row-value">
              {{ $fmtVnd($order->shipping_fee_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="5" class="totals-row-label">
              Giảm giá
            </td>
            <td class="totals-row-value">
              {{ $fmtVnd($order->discount_vnd) }}
            </td>
          </tr>
          <tr>
            <td colspan="5" class="totals-row-label totals-row-total">
              Tổng cộng
            </td>
            <td class="totals-row-value totals-row-total">
              {{ $fmtVnd($order->grand_total_vnd) }}
            </td>
          </tr>
        </tfoot>
      </table>

      <div class="footer-note">
        Lưu ý: Email này dùng để xác nhận đơn hàng và không có giá trị thay thế hóa đơn VAT.
      </div>
    </div>
  </div>
</body>

</html>
