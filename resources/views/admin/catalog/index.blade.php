@extends('layouts.admin')

@section('title','Catalog: Category & Brand')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Danh mục & NSX</li>
  </ol>
</nav>

<ul class="nav nav-tabs mb-3" id="catalogTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="category-tab" data-bs-toggle="tab" data-bs-target="#category-pane" type="button" role="tab">
      Danh mục
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="brand-tab" data-bs-toggle="tab" data-bs-target="#brand-pane" type="button" role="tab">
      NSX
    </button>
  </li>
</ul>

<div class="tab-content" id="catalogTabsContent">
  {{-- ================== TAB 1: CATEGORY ================== --}}
  <div class="tab-pane fade show active" id="category-pane" role="tabpanel" aria-labelledby="category-tab">
    <div class="table-in-clip">
      <div class="card shadow-sm table-in">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Danh sách category</h5>
          <form method="GET" class="d-flex align-items-center">
            <label class="me-2 mb-0">Hiển thị</label>
            <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_cat" onchange="this.form.submit()">
              <option value="10" {{ request('per_page_cat')==10?'selected':'' }}>10</option>
              <option value="20" {{ request('per_page_cat')==20?'selected':'' }}>20</option>
              <option value="50" {{ request('per_page_cat')==50?'selected':'' }}>50</option>
            </select>
            <input type="hidden" name="tab" value="category">
            <input type="hidden" name="brand_keyword" value="{{ request('brand_keyword') }}">
            <input type="hidden" name="brand_status" value="{{ request('brand_status') }}">
            <input type="hidden" name="per_page_brand" value="{{ request('per_page_brand') }}">
          </form>
        </div>

        <div class="card-body">
          {{-- Filters --}}
          <form method="GET" class="row g-2 mb-3 filter-form">
            <div class="col-md-4">
              <input type="text" name="cat_keyword" class="form-control" placeholder="Tìm tên / slug" value="{{ request('cat_keyword') }}">
            </div>
            <div class="col-md-2">
              <select name="cat_status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('cat_status')==='ACTIVE'?'selected':'' }}>Đang hoạt động</option>
                <option value="INACTIVE" {{ request('cat_status')==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
              </select>
            </div>
            <div class="col-md-1 d-grid">
              <button type="submit" class="btn btn-primary btn-admin">Lọc</button>
            </div>
            <input type="hidden" name="tab" value="category">
            <input type="hidden" name="brand_keyword" value="{{ request('brand_keyword') }}">
            <input type="hidden" name="brand_status" value="{{ request('brand_status') }}">
            <input type="hidden" name="per_page_brand" value="{{ request('per_page_brand') }}">
          </form>

          {{-- Bulk actions --}}
          <div class="d-flex justify-content-between mb-2">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-danger btn-admin" id="catBtnBulkDelete" disabled>Xoá đã chọn</button>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiCategoryModal">Thêm danh mục</button>
            </div>
          </div>

          {{-- Table --}}
          <div class="table-responsive">
            <table id="categoryTable" class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th class="checkAllWidth"><input type="checkbox" id="cat_check_all"></th>
                  <th class="STT_Width">#</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th class="statusWidth">Trạng thái</th>
                  <th class="createTimeWidth">Tạo lúc</th>
                  <th class="actionWihdth text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($categories as $index => $cat)
                <tr>
                  <td><input type="checkbox" class="cat-row-checkbox" value="{{ $cat->id }}"></td>
                  <td>{{ $categories->firstItem() + $index }}</td>
                  <td>{{ $cat->name }}</td>
                  <td>{{ $cat->slug }}</td>
                  <td>
                    @if($cat->status == 'ACTIVE')
                    <span class="badge bg-success">Đang hoạt động</span>
                    @else
                    <span class="badge bg-danger">Ngừng hoạt động</span>
                    @endif
                  </td>
                  <td>{{ $cat->created_at?->format('d/m/Y H:i') }}</td>
                  <td class="text-center">
                    <button type="button"
                      class="btn btn-sm btn-success btnCateEdit"
                      data-update-url="{{ route('admin.categories.update', $cat->id) }}"
                      data-name="{{ $cat->name }}"
                      data-slug="{{ $cat->slug }}"
                      data-description="{{ $cat->description }}"
                      data-status="{{ $cat->status }}"
                      data-image="{{ $cat->image ? Storage::url('categories/'.$cat->image) : '' }}">
                      <i class="fa fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" class="d-inline catDeleteForm">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger btnCateDelete">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>

                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $categories->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ================== TAB 2: BRAND ================== --}}
  <div class="tab-pane fade" id="brand-pane" role="tabpanel" aria-labelledby="brand-tab">
    <div class="table-in-clip">
      <div class="card shadow-sm table-in">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Danh sách brand</h5>
          <form method="GET" class="d-flex align-items-center">
            <label class="me-2 mb-0">Hiển thị</label>
            <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_brand" onchange="this.form.submit()">
              <option value="10" {{ request('per_page_brand')==10?'selected':'' }}>10</option>
              <option value="20" {{ request('per_page_brand')==20?'selected':'' }}>20</option>
              <option value="50" {{ request('per_page_brand')==50?'selected':'' }}>50</option>
            </select>
            <input type="hidden" name="tab" value="brand">
            {{-- bảo toàn filter category --}}
            <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
            <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
            <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
          </form>
        </div>

        <div class="card-body">
          {{-- Filters --}}
          <form method="GET" class="row g-2 mb-3 filter-form">
            <div class="col-md-4">
              <input type="text" name="brand_keyword" class="form-control" placeholder="Tìm tên / slug" value="{{ request('brand_keyword') }}">
            </div>
            <div class="col-md-2 select2CustomWidth">
              <select name="brand_status" class="form-select setupSelect2">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ACTIVE" {{ request('brand_status')==='ACTIVE'?'selected':'' }}>Đang hoạt động</option>
                <option value="INACTIVE" {{ request('brand_status')==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
              </select>
            </div>
            <div class="col-md-1 d-grid">
              <button type="submit" class="btn btn-primary btn-admin">Lọc</button>
            </div>
            <input type="hidden" name="tab" value="brand">
            {{-- bảo toàn filter category --}}
            <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
            <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
            <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
          </form>

          {{-- Bulk actions --}}
          <div class="d-flex justify-content-between mb-2">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-danger btn-admin" id="brandBtnBulkDelete" disabled>Xoá đã chọn</button>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiBrandModal">Thêm NSX</button>
            </div>
          </div>

          {{-- Table --}}
          <div class="table-responsive">
            <table id="brandTable" class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th class="checkAllWidth"><input type="checkbox" id="brand_check_all"></th>
                  <th class="STT_Width">#</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th class="statusWidth">Trạng thái</th>
                  <th class="createTimeWidth">Tạo lúc</th>
                  <th class="actionWihdth text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($brands as $index => $brand)
                <tr>
                  <td><input type="checkbox" class="brand-row-checkbox" value="{{ $brand->id }}"></td>
                  <td>{{ $brands->firstItem() + $index }}</td>
                  <td>{{ $brand->name }}</td>
                  <td>{{ $brand->slug }}</td>
                  <td>
                    @if($brand->status === 'ACTIVE')
                    <span class="badge bg-success">Đang hoạt động</span>
                    @else
                    <span class="badge bg-secondary">Ngừng hoạt động</span>
                    @endif
                  </td>
                  <td>{{ $brand->created_at?->format('d/m/Y H:i') }}</td>
                  <td class="text-center">
                    <button type="button"
                      class="btn btn-sm btn-success btnBrandEdit"
                      data-update-url="{{ route('admin.brands.update', $brand->id) }}"
                      data-name="{{ $brand->name }}"
                      data-slug="{{ $brand->slug }}"
                      data-description="{{ $brand->description }}"
                      data-status="{{ $brand->status }}"
                      data-image="{{ $brand->image ? Storage::url($brand->image) : '' }}">
                      <i class="fa fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.brands.destroy', $brand->id) }}" class="d-inline brandDeleteForm">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger btnBrandDelete">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $brands->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Bulk forms --}}
<form id="catBulkForm" method="POST" action="{{ route('admin.categories.bulk-delete') }}" class="d-none">
  @csrf
  <div id="catBulkIds"></div>
</form>
<form id="brandBulkForm" method="POST" action="{{ route('admin.brands.bulk-delete') }}" class="d-none">
  @csrf
  <div id="brandBulkIds"></div>
</form>

<div id="__formState"
  data-has-errors="{{ $errors->any() ? 1 : 0 }}"
  data-which="{{ old('__form') }}"
  data-mode="{{ old('__mode','create') }}"
  style="display:none"></div>

{{-- Modals: Category & Brand --}}
@include('partials.ui.catalog.category-modal')
@include('partials.ui.catalog.brand-modal')
@include('partials.ui.confirm-modal')
@endsection
@push('scripts')
<script>
  function getTabFromURL() {
    const u = new URL(location.href);
    const t = u.searchParams.get('tab');
    return ['category', 'brand'].includes(t) ? t : 'category';
  }

  function setTabInURL(tab, replace = false) {
    const u = new URL(location.href);
    u.searchParams.set('tab', tab);
    replace ? history.replaceState(null, '', u) : history.pushState(null, '', u);
  }

  function showTab(tab) {
    const trigger = document.querySelector(`[data-bs-target="#${tab}-pane"]`);
    if (!trigger) return;
    new bootstrap.Tab(trigger).show();
  }

  document.addEventListener('DOMContentLoaded', () => {
    // đúng tab theo URL
    showTab(getTabFromURL());
    const tabs = document.getElementById('catalogTabs');
    if (tabs) {
      tabs.addEventListener('shown.bs.tab', (e) => {
        const pane = e.target.getAttribute('data-bs-target');
        const tab = pane.includes('brand') ? 'brand' : 'category';
        setTabInURL(tab);
      });
    }
    window.addEventListener('popstate', () => showTab(getTabFromURL()));
    setTabInURL(getTabFromURL(), true);
  });
</script>

<script>
  // @ts-nocheck
  function makeHiddenInputs(container, name, values) {
    container.innerHTML = '';
    values.forEach(v => {
      const i = document.createElement('input');
      i.type = 'hidden';
      i.name = name;
      i.value = v;
      container.appendChild(i);
    });
  }

  function getCheckedValues(tableSel) {
    const cbs = document.querySelectorAll(`${tableSel} tbody input[type=checkbox]:checked`);
    return Array.from(cbs).map(x => x.value);
  }

  function toggleMaster(masterSel, rowSel) {
    const master = document.querySelector(masterSel);
    const rows = document.querySelectorAll(rowSel);
    master?.addEventListener('change', () => {
      rows.forEach(cb => { cb.checked = master.checked; });
      master.indeterminate = false;
      updateBulkButtons();
    });
  }

  function updateBulkButtons() {
    const catAny = getCheckedValues('#categoryTable').length > 0;
    const brandAny = getCheckedValues('#brandTable').length > 0;
    const catBtn = document.getElementById('catBtnBulkDelete');
    const brandBtn = document.getElementById('brandBtnBulkDelete');
    if (catBtn) catBtn.disabled = !catAny;
    if (brandBtn) brandBtn.disabled = !brandAny;
  }

  document.addEventListener('DOMContentLoaded', function() {
    // ================== Master & bulk ==================
    toggleMaster('#cat_check_all', '#categoryTable tbody .cat-row-checkbox');
    toggleMaster('#brand_check_all', '#brandTable tbody .brand-row-checkbox');
    document.querySelector('#categoryTable')?.addEventListener('change', (e) => {
      if (e.target.classList?.contains('cat-row-checkbox')) updateBulkButtons();
    });
    document.querySelector('#brandTable')?.addEventListener('change', (e) => {
      if (e.target.classList?.contains('brand-row-checkbox')) updateBulkButtons();
    });
    updateBulkButtons();

    // Bulk delete Category
    document.getElementById('catBtnBulkDelete')?.addEventListener('click', async () => {
      const ids = getCheckedValues('#categoryTable');
      if (!ids.length) return;
      const ok = await (window.UIConfirm ? UIConfirm({
        title: 'Xác nhận xoá',
        message: `Bạn sắp xoá <b>${ids.length}</b> category.`
      }) : Promise.resolve(confirm('Xoá các category đã chọn?')));
      if (!ok) return;
      const form = document.getElementById('catBulkForm');
      const box = document.getElementById('catBulkIds');
      makeHiddenInputs(box, 'ids[]', ids);
      form.submit();
    });
    // Xoá category đơn
    document.querySelectorAll('.btnCateDelete').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const form = btn.closest('form');
        const ok = await (window.UIConfirm ? UIConfirm({
          title: 'Xác nhận xoá',
          message: 'Bạn có chắc chắn muốn xoá category này?'
        }) : Promise.resolve(confirm('Xoá category này?')));
        if (ok) form.submit();
      });
    });

    // Bulk delete Brand
    document.getElementById('brandBtnBulkDelete')?.addEventListener('click', async () => {
      const ids = getCheckedValues('#brandTable');
      if (!ids.length) return;
      const ok = await (window.UIConfirm ? UIConfirm({
        title: 'Xác nhận xoá',
        message: `Bạn sắp xoá <b>${ids.length}</b> brand.`
      }) : Promise.resolve(confirm('Xoá các brand đã chọn?')));
      if (!ok) return;
      const form = document.getElementById('brandBulkForm');
      const box = document.getElementById('brandBulkIds');
      makeHiddenInputs(box, 'ids[]', ids);
      form.submit();
    });
    // Xoá brand đơn
    document.querySelectorAll('.btnBrandDelete').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const form = btn.closest('form');
        const ok = await (window.UIConfirm ? UIConfirm({
          title: 'Xác nhận xoá',
          message: 'Bạn có chắc chắn muốn xoá brand này?'
        }) : Promise.resolve(confirm('Xoá brand này?')));
        if (ok) form.submit();
      });
    });

    // ================== CATEGORY MODAL ==================
    const catModal = document.getElementById('uiCategoryModal');
    const catForm  = document.getElementById('uiCategoryForm');
    const catImg   = document.getElementById('cat_image');
    const catPrev  = document.getElementById('cat_image_preview');
    const catPH    = document.getElementById('cat_image_placeholder');
    const catTitle = document.getElementById('catModalTitle');

    function setCatPreview(src) {
      if (src) {
        catPrev.src = src;
        catPrev.classList.remove('d-none');
        catPH.classList.add('d-none');
      } else {
        catPrev.src = '';
        catPrev.classList.add('d-none');
        catPH.classList.remove('d-none');
      }
    }

    // Nút "Thêm category"
    document.querySelectorAll('[data-bs-target="#uiCategoryModal"]').forEach(btn => {
      btn.addEventListener('click', () => {
        catForm.querySelector('[name="__mode"]').value = 'create';
        catForm.action = "{{ route('admin.categories.store') }}";
        catForm.querySelector('[name=_method]').value = 'POST';
        if (catTitle) catTitle.textContent = 'Thêm category';
        setCatPreview('');
      });
    });

    // Nút "Sửa category"
    document.querySelectorAll('.btnCateEdit').forEach(btn => {
      btn.addEventListener('click', () => {
        catForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        catForm.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

        catForm.querySelector('[name="__mode"]').value = 'edit';
        catForm.action = btn.dataset.updateUrl;
        catForm.querySelector('[name=_method]').value = 'PUT';
        catForm.querySelector('[name="__form"]').value = 'category';

        catForm.querySelector('#cat_name').value        = btn.dataset.name || '';
        catForm.querySelector('#cat_slug').value        = btn.dataset.slug || '';
        catForm.querySelector('#cat_description').value = btn.dataset.description || '';
        catForm.querySelector('#cat_status').value      = (btn.dataset.status || 'ACTIVE');
        if (window.jQuery && $.fn?.select2) $('#cat_status').trigger('change.select2');

        setCatPreview(btn.dataset.image || '');
        try { if (catImg) catImg.value = ''; } catch(_) {}

        if (catTitle) catTitle.textContent = 'Cập nhật category';
        bootstrap.Modal.getOrCreateInstance(catModal).show();
      });
    });

    catImg?.addEventListener('change', () => {
      const f = catImg.files?.[0];
      setCatPreview(f ? URL.createObjectURL(f) : '');
    });

    // ================== BRAND MODAL ==================
    const brandModal = document.getElementById('uiBrandModal');
    const brandForm  = document.getElementById('uiBrandForm');
    const brandImg   = document.getElementById('brand_image');
    const brandPrev  = document.getElementById('brand_image_preview');
    const brandPH    = document.getElementById('brand_image_placeholder');
    const brandTitle = document.getElementById('brandModalTitle');

    function setBrandPreview(src) {
      if (!brandPrev || !brandPH) return;
      if (src) {
        brandPrev.src = src;
        brandPrev.classList.remove('d-none');
        brandPH.classList.add('d-none');
      } else {
        brandPrev.src = '';
        brandPrev.classList.add('d-none');
        brandPH.classList.remove('d-none');
      }
    }

    // Nút "Thêm brand"
    document.querySelectorAll('[data-bs-target="#uiBrandModal"]').forEach(btn => {
      btn.addEventListener('click', () => {
        brandForm.querySelector('[name="__mode"]').value = 'create';
        brandForm.action = "{{ route('admin.brands.store') }}";
        brandForm.querySelector('[name=_method]').value = 'POST';
        if (brandTitle) brandTitle.textContent = 'Thêm brand';
        setBrandPreview('');
      });
    });

    // Nút "Sửa brand"
    document.querySelectorAll('.btnBrandEdit').forEach(btn => {
      btn.addEventListener('click', () => {
        brandForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        brandForm.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');

        brandForm.querySelector('[name="__mode"]').value = 'edit';
        brandForm.action = btn.dataset.updateUrl;
        brandForm.querySelector('[name=_method]').value = 'PUT';
        brandForm.querySelector('[name="__form"]').value = 'brand';

        brandForm.querySelector('#brand_name').value        = btn.dataset.name || '';
        brandForm.querySelector('#brand_slug').value        = btn.dataset.slug || '';
        brandForm.querySelector('#brand_description').value = btn.dataset.description || '';
        brandForm.querySelector('#brand_status').value      = btn.dataset.status || 'ACTIVE';
        if (window.jQuery && $.fn?.select2) $('#brand_status').trigger('change.select2');

        setBrandPreview(btn.dataset.image || '');
        try { if (brandImg) brandImg.value = ''; } catch(_) {}

        if (brandTitle) brandTitle.textContent = 'Cập nhật brand';
        bootstrap.Modal.getOrCreateInstance(brandModal).show();
      });
    });

    brandImg?.addEventListener('change', () => {
      const f = brandImg.files?.[0];
      setBrandPreview(f ? URL.createObjectURL(f) : '');
    });

    // ================== RE-OPEN MODALS WHEN VALIDATION ERROR ==================
    // Gợi ý: bổ sung data-mode vào #__formState trong Blade:
    // <div id="__formState" data-has-errors="{{ $errors->any()?1:0 }}" data-which="{{ old('__form') }}" data-mode="{{ old('__mode','create') }}" style="display:none"></div>
    const __stateEl   = document.getElementById('__formState');
    const __hasErrors = __stateEl?.dataset.hasErrors === '1';
    const __which     = (__stateEl?.dataset.which || null);
    const __mode      = (__stateEl?.dataset.mode || 'create');

    if (__hasErrors && __which === 'category') {
      catForm.action = "{{ route('admin.categories.store') }}";
      catForm.querySelector('[name=_method]').value = 'POST';
      catForm.querySelector('[name="__form"]').value = 'category';
      setCatPreview('');
      if (catTitle) catTitle.textContent = (__mode === 'edit') ? 'Cập nhật category' : 'Thêm category';
      bootstrap.Modal.getOrCreateInstance(catModal).show();
      const trigger = document.querySelector('[data-bs-target="#category-pane"]');
      if (trigger) new bootstrap.Tab(trigger).show();
    }

    if (__hasErrors && __which === 'brand') {
      brandForm.action = "{{ route('admin.brands.store') }}";
      brandForm.querySelector('[name=_method]').value = 'POST';
      brandForm.querySelector('[name="__form"]').value = 'brand';
      setBrandPreview('');
      if (brandTitle) brandTitle.textContent = (__mode === 'edit') ? 'Cập nhật brand' : 'Thêm brand';
      bootstrap.Modal.getOrCreateInstance(brandModal).show();
      const trigger = document.querySelector('[data-bs-target="#brand-pane"]');
      if (trigger) new bootstrap.Tab(trigger).show();
    }
  });
</script>
@endpush