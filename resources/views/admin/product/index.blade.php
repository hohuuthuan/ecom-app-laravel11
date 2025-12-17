@extends('layouts.admin')

@section('title','Products: Danh sách sản phẩm')

@section('body_class','create-product-page')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Sản phẩm</li>
  </ol>
</nav>

<div class="table-in-clip">
  <div class="card shadow-sm table-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh sách sản phẩm</h5>

      <form method="GET" class="d-flex align-items-center">
        <label class="me-2 mb-0">Hiển thị</label>
        @php($pp = (int)request('per_page_product', 10))
        <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_product" onchange="this.form.submit()">
          <option value="10" {{ $pp===10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ $pp===20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ $pp===50 ? 'selected' : '' }}>50</option>
        </select>

        {{-- preserve current filters when changing page size --}}
        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
        <input type="hidden" name="author_id" value="{{ request('author_id') }}">
        <input type="hidden" name="publisher_id" value="{{ request('publisher_id') }}">
      </form>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form method="GET" class="row g-2 mb-3 filter-form">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-6 searchProduct">
              <label for="keyword" class="form-label mb-1 label-filter-admin-product">Tìm kiếm</label>
              <input
                id="keyword"
                type="text"
                name="keyword"
                class="form-control"
                placeholder="Tìm tên / ISBN / slug"
                value="{{ request('keyword') }}">
            </div>

            <div class="col-md-3 d-flex align-items-center gap-2">
              <button type="submit" class="btn-admin btn-submit-filter-admin-product">
                <i class="fa fa-search me-1"></i> Tìm kiếm
              </button>

              <a href="{{ route('admin.product.index') }}" class="btn btn-outline-secondary btn-submit-filter-admin-product">
                <i class="fa fa-eraser me-1"></i> Xóa lọc
              </a>
            </div>
          </div>

          <div class="row searchProduct">
            <div class="col-md-2">
              <label for="category_id" class="form-label mb-1 label-filter-admin-product">Danh mục</label>
              <select id="category_id" name="category_id" class="form-select setupSelect2">
                <option value="">-- Tất cả danh mục --</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') === (string)$c->id ? 'selected' : '' }}>
                  {{ $c->name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label for="author_id" class="form-label mb-1 label-filter-admin-product">Tác giả</label>
              <select id="author_id" name="author_id" class="form-select setupSelect2">
                <option value="">-- Tất cả tác giả --</option>
                @foreach($authors as $a)
                <option value="{{ $a->id }}" {{ request('author_id') === (string)$a->id ? 'selected' : '' }}>
                  {{ $a->name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label for="publisher_id" class="form-label mb-1 label-filter-admin-product">Nhà xuất bản</label>
              <select id="publisher_id" name="publisher_id" class="form-select setupSelect2">
                <option value="">-- Tất cả NXB --</option>
                @foreach($publishers as $p)
                <option value="{{ $p->id }}" {{ request('publisher_id') === (string)$p->id ? 'selected' : '' }}>
                  {{ $p->name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <label for="status" class="form-label mb-1 label-filter-admin-product">Trạng thái</label>
              <select id="status" name="status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('status')==='ACTIVE'?'selected':'' }}>Đang bán</option>
                <option value="INACTIVE" {{ request('status')==='INACTIVE'?'selected':'' }}>Ẩn</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-2">
              <label for="price_min" class="form-label mb-1 label-filter-admin-product">Giá từ</label>
              <input id="price_min" type="number" name="price_min" class="form-control" placeholder="0" value="{{ request('price_min') }}" min="0" step="1">
            </div>
            <div class="col-md-2">
              <label for="price_max" class="form-label mb-1 label-filter-admin-product">Giá đến</label>
              <input id="price_max" type="number" name="price_max" class="form-control" placeholder="∞" value="{{ request('price_max') }}" min="0" step="1">
            </div>
            <div class="col-md-2">
              <label for="stock_min" class="form-label mb-1 label-filter-admin-product">Tồn từ</label>
              <input id="stock_min" type="number" name="stock_min" class="form-control" placeholder="0" value="{{ request('stock_min') }}" min="0" step="1">
            </div>
            <div class="col-md-2">
              <label for="stock_max" class="form-label mb-1 label-filter-admin-product">Tồn đến</label>
              <input id="stock_max" type="number" name="stock_max" class="form-control" placeholder="∞" value="{{ request('stock_max') }}" min="0" step="1">
            </div>
          </div>
        </div>

      </form>

      {{-- Bulk actions --}}
      <div class="d-flex justify-content-between mb-2">
        <form
          id="productBulkForm"
          method="POST"
          action="{{ route('admin.product.bulk-update') }}"
          class="d-flex flex-wrap align-items-end gap-2">
          @csrf
          <input type="hidden" name="action" id="product_bulk_action" value="">
          <input type="hidden" name="status" id="product_bulk_status" value="">
          <div id="productBulkIds"></div>

          <div>
            <label for="bulk_discount_percent" class="form-label mb-1 label-filter-admin-product">Giảm giá (%)</label>
            <input
              id="bulk_discount_percent"
              type="number"
              name="discount_percent"
              class="form-control form-control-sm"
              placeholder="0 - 100"
              min="0"
              max="100"
              step="1">
          </div>

          <button style="height: 32px;" type="button" class="btn-admin" id="btnBulkDiscount" disabled>
            <i class="fa fa-percent me-1"></i>Áp dụng
          </button>
          <button type="button" class="btn btn-sm btn-secondary" id="btnBulkClearDiscount" disabled>
            Bỏ giảm
          </button>

          <span class="vr mx-1 d-none d-lg-block" aria-hidden="true"></span>

          <button style="height: 32px;" type="button" class="btn-admin" id="btnBulkActive" data-bulk-status="ACTIVE" disabled>
            <i class="fa fa-eye me-1"></i>Đang bán
          </button>
          <button type="button" class="btn btn-sm btn-secondary" id="btnBulkInactive" data-bulk-status="INACTIVE" disabled>
            <i class="fa fa-eye-slash me-1"></i>Ẩn
          </button>
        </form>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.product.create') }}"><button type="button" class="btn-admin" data-bs-toggle="modal" data-bs-target="#uiProductModal"><i class="fa fa-plus me-1"></i>Thêm sản phẩm</button></a>
        </div>
      </div>

      <div class="table-responsive">
        <table id="productTable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="checkAllWidth"><input type="checkbox" id="product_check_all"></th>
              <th class="STT_Width">#</th>
              <th>TÊN</th>
              <!-- <th>SLUG</th> -->
              <th>MÃ ĐỊNH DANH</th>
              <th>DANH MỤC</th>
              <th>TÁC GIẢ</th>
              <th>NHÀ XUẤT BẢN</th>
              <th>GIÁ</th>
              <th class="statusWidth">TRẠNG THÁI</th>
              <th class="actionWidth text-center">THAO TÁC</th>
            </tr>
          </thead>
          <tbody>
            @forelse($products as $idx => $p)
            <tr>
              <td><input type="checkbox" class="product-row-checkbox" value="{{ $p->id }}"></td>
              <td>{{ $products->firstItem() + $idx }}</td>
              <td>{{ $p->title }}</td>
              <!-- <td>{{ $p->slug }}</td> -->
              <td><code>{{ $p->isbn ?? '—' }}</code></td>
              <td>{{ $p->categories?->pluck('name')->join(', ') }}</td>
              <td>{{ $p->authors?->pluck('name')->join(', ') }}</td>
              <td>{{ $p->publisher?->name }}</td>
              <td>
                @php($price = (int)($p->selling_price_vnd ?? 0))
                @php($dp = (int)($p->discount_percent ?? 0))
                @php($dp = max(0, min(100, $dp)))
                @php($final = $dp > 0 ? (int) floor($price * (100 - $dp) / 100) : $price)

                @if($dp > 0)

                <div class="fw-semibold text-success">
                  {{ number_format($final,0,',','.') }}₫
                  <span class="badge bg-warning text-dark ms-1">-{{ $dp }}%</span>
                </div>
                @else
                <div class="fw-semibold">
                  {{ number_format($price,0,',','.') }}₫
                </div>
                @endif
                <div class="small text-muted text-decoration-line-through">
                  {{ number_format($price,0,',','.') }}₫
                </div>
              </td>

              <td>
                @if($p->status === 'ACTIVE')
                <span class="badge bg-success">Đang bán</span>
                @else
                <span class="badge bg-secondary">Ẩn</span>
                @endif
              </td>
              <td class="text-center">
                <a href="{{ route('admin.product.edit', $p->id) }}">
                  <button type="button" class="btn btn-sm btn-success btnProductEdit">
                    <i class="fa fa-edit"></i>
                  </button>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="12" class="text-center text-muted">Không có dữ liệu</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

@include('partials.ui.confirm-modal')

@push('scripts')
<script>
  (function() {
    'use strict';

    function qs(selector, root) {
      return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
      return Array.from((root || document).querySelectorAll(selector));
    }

    function normalizeConfirmText(html) {
      return String(html || '').replace(/<[^>]*>/g, '').trim();
    }

    function confirmUI(title, messageHtml) {
      if (typeof window.UIConfirm === 'function') {
        return window.UIConfirm({
          title: title,
          message: messageHtml
        });
      }
      return Promise.resolve(confirm(normalizeConfirmText(messageHtml)));
    }

    function getRowCheckboxes() {
      return qsa('#productTable tbody .product-row-checkbox');
    }

    function getCheckedIds() {
      return qsa('#productTable tbody .product-row-checkbox:checked').map(function(cb) {
        return cb.value;
      });
    }

    function makeHiddenInputs(container, name, values) {
      container.innerHTML = '';
      values.forEach(function(v) {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = name;
        i.value = v;
        container.appendChild(i);
      });
    }

    function setRowCheckedStyle(checkbox) {
      const row = checkbox ? checkbox.closest('tr') : null;
      if (!row) {
        return;
      }
      row.classList.toggle('row-checked', checkbox.checked);
    }

    function syncAllRowCheckedStyles() {
      getRowCheckboxes().forEach(function(cb) {
        setRowCheckedStyle(cb);
      });
    }

    function updateMasterState(master, rows) {
      const checked = rows.filter(function(cb) {
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

    function updateBulkButtons(enabled) {
      const ids = getCheckedIds();
      const on = enabled ?? ids.length > 0;

      const discountBtn = qs('#btnBulkDiscount');
      const clearDiscountBtn = qs('#btnBulkClearDiscount');
      const activeBtn = qs('#btnBulkActive');
      const inactiveBtn = qs('#btnBulkInactive');

      if (discountBtn) {
        discountBtn.disabled = !on;
      }
      if (clearDiscountBtn) {
        clearDiscountBtn.disabled = !on;
      }
      if (activeBtn) {
        activeBtn.disabled = !on;
      }
      if (inactiveBtn) {
        inactiveBtn.disabled = !on;
      }
    }

    function submitBulk(action, payload) {
      const form = qs('#productBulkForm');
      const idsBox = qs('#productBulkIds');
      const actionEl = qs('#product_bulk_action');
      const statusEl = qs('#product_bulk_status');

      const ids = getCheckedIds();
      if (!ids.length) {
        return;
      }

      if (!form || !idsBox || !actionEl || !statusEl) {
        return;
      }

      actionEl.value = action;
      statusEl.value = '';

      if (action === 'STATUS') {
        statusEl.value = payload.status;
      }

      if (action === 'DISCOUNT') {
        const input = qs('#bulk_discount_percent');
        if (input) {
          input.value = String(payload.discount_percent);
        }
      }

      makeHiddenInputs(idsBox, 'ids[]', ids);
      form.submit();
    }

    function bindBulkButtons() {
      const bulkDiscountBtn = qs('#btnBulkDiscount');
      if (bulkDiscountBtn) {
        bulkDiscountBtn.addEventListener('click', async function() {
          const ids = getCheckedIds();
          if (!ids.length) {
            return;
          }

          const input = qs('#bulk_discount_percent');
          const raw = input ? input.value : '';
          const percent = Number.parseInt(String(raw), 10);

          if (!Number.isFinite(percent) || percent < 0 || percent > 100) {
            alert('Vui lòng nhập % giảm giá trong khoảng 0 - 100.');
            return;
          }

          const ok = await confirmUI(
            'Xác nhận cập nhật',
            `Bạn sắp áp dụng giảm giá <b>${percent}%</b> cho <b>${ids.length}</b> sản phẩm.`
          );
          if (!ok) {
            return;
          }

          submitBulk('DISCOUNT', {
            discount_percent: percent
          });
        });
      }

      const bulkClearDiscountBtn = qs('#btnBulkClearDiscount');
      if (bulkClearDiscountBtn) {
        bulkClearDiscountBtn.addEventListener('click', async function() {
          const ids = getCheckedIds();
          if (!ids.length) {
            return;
          }

          const ok = await confirmUI(
            'Xác nhận cập nhật',
            `Bạn sắp <b>bỏ giảm giá</b> (0%) cho <b>${ids.length}</b> sản phẩm.`
          );
          if (!ok) {
            return;
          }

          submitBulk('DISCOUNT', {
            discount_percent: 0
          });
        });
      }

      qsa('[data-bulk-status]').forEach(function(btn) {
        btn.addEventListener('click', async function() {
          const ids = getCheckedIds();
          if (!ids.length) {
            return;
          }

          const status = String(btn.getAttribute('data-bulk-status') || '').toUpperCase();
          if (!['ACTIVE', 'INACTIVE'].includes(status)) {
            return;
          }

          const label = status === 'ACTIVE' ? 'Đang bán' : 'Ẩn';
          const ok = await confirmUI(
            'Xác nhận cập nhật',
            `Bạn sắp cập nhật trạng thái <b>${label}</b> cho <b>${ids.length}</b> sản phẩm.`
          );
          if (!ok) {
            return;
          }

          submitBulk('STATUS', {
            status: status
          });
        });
      });
    }

    function bindCheckboxEvents() {
      const master = qs('#product_check_all');
      const table = qs('#productTable');

      if (master) {
        master.addEventListener('change', function() {
          const rows = getRowCheckboxes();
          rows.forEach(function(cb) {
            cb.checked = master.checked;
            setRowCheckedStyle(cb);
          });
          master.indeterminate = false;
          updateBulkButtons(master.checked);
        });
      }

      if (table) {
        table.addEventListener('change', function(e) {
          const target = e.target;
          if (!target || !target.classList || !target.classList.contains('product-row-checkbox')) {
            return;
          }

          setRowCheckedStyle(target);

          if (master) {
            updateMasterState(master, getRowCheckboxes());
          }
          updateBulkButtons();
        });
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      bindCheckboxEvents();
      bindBulkButtons();
      syncAllRowCheckedStyles();
      updateBulkButtons(false);
    });
  })();
</script>
@endpush
@endsection