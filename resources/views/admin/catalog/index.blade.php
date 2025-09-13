@extends('layouts.admin')

@section('title','Catalog: Category & Brand')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Catalog</li>
  </ol>
</nav>

{{-- Tabs Bootstrap --}}
<ul class="nav nav-tabs mb-3" id="catalogTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="category-tab" data-bs-toggle="tab" data-bs-target="#category-pane" type="button" role="tab">
      Category
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="brand-tab" data-bs-toggle="tab" data-bs-target="#brand-pane" type="button" role="tab">
      Brand
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
              <option value="10"  {{ request('per_page_cat')==10?'selected':'' }}>10</option>
              <option value="20"  {{ request('per_page_cat')==20?'selected':'' }}>20</option>
              <option value="50"  {{ request('per_page_cat')==50?'selected':'' }}>50</option>
            </select>
            {{-- giữ tab khi submit --}}
            <input type="hidden" name="tab" value="category">
            {{-- bảo toàn bộ lọc brand nếu có --}}
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
                <option value="ACTIVE"   {{ request('cat_status')==='ACTIVE'?'selected':'' }}>Kích hoạt</option>
                <option value="INACTIVE" {{ request('cat_status')==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
              </select>
            </div>
            <div class="col-md-1 d-grid">
              <button type="submit" class="btn btn-primary btn-admin">Lọc</button>
            </div>
            {{-- giữ tab + bảo toàn filter brand --}}
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
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiCategoryModal">Thêm category</button>
            </div>
          </div>

          {{-- Table --}}
          <div class="table-responsive">
            <table id="categoryTable" class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width:42px"><input type="checkbox" id="cat_check_all"></th>
                  <th style="width:60px">#</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th style="width:140px">Trạng thái</th>
                  <th style="width:180px">Tạo lúc</th>
                  <th class="text-center" style="width:140px">Thao tác</th>
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
                    @if($cat->status === 'ACTIVE')
                      <span class="badge bg-success">Kích hoạt</span>
                    @else
                      <span class="badge bg-secondary">Ngừng hoạt động</span>
                    @endif
                  </td>
                  <td>{{ $cat->created_at?->format('d/m/Y H:i') }}</td>
                  <td class="text-center">
                    <button type="button"
                      class="btn btn-sm btn-outline-primary btnCateEdit"
                      data-update-url="{{ route('admin.categories.update', $cat->id) }}"
                      data-name="{{ $cat->name }}"
                      data-slug="{{ $cat->slug }}"
                      data-description="{{ $cat->description }}"
                      data-status="{{ $cat->status }}"
                      data-image="{{ $cat->image ? Storage::url($cat->image) : '' }}">
                      Sửa
                    </button>
                    <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xoá category này?')">Xoá</button>
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
              <option value="10"  {{ request('per_page_brand')==10?'selected':'' }}>10</option>
              <option value="20"  {{ request('per_page_brand')==20?'selected':'' }}>20</option>
              <option value="50"  {{ request('per_page_brand')==50?'selected':'' }}>50</option>
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
                <option value="ACTIVE"   {{ request('brand_status')==='ACTIVE'?'selected':'' }}>Kích hoạt</option>
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
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uiBrandModal">Thêm brand</button>
            </div>
          </div>

          {{-- Table --}}
          <div class="table-responsive">
            <table id="brandTable" class="table table-bordered table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width:42px"><input type="checkbox" id="brand_check_all"></th>
                  <th style="width:60px">#</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th style="width:140px">Trạng thái</th>
                  <th style="width:180px">Tạo lúc</th>
                  <th class="text-center" style="width:140px">Thao tác</th>
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
                      <span class="badge bg-success">ACTIVE</span>
                    @else
                      <span class="badge bg-secondary">INACTIVE</span>
                    @endif
                  </td>
                  <td>{{ $brand->created_at?->format('d/m/Y H:i') }}</td>
                  <td class="text-center">
                    <button type="button"
                      class="btn btn-sm btn-outline-primary btnBrandEdit"
                      data-update-url="{{ route('admin.brands.update', $brand->id) }}"
                      data-name="{{ $brand->name }}"
                      data-slug="{{ $brand->slug }}"
                      data-description="{{ $brand->description }}"
                      data-status="{{ $brand->status }}"
                      data-image="{{ $brand->image ? Storage::url($brand->image) : '' }}">
                      Sửa
                    </button>
                    <form method="POST" action="{{ route('admin.brands.destroy', $brand->id) }}" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xoá brand này?')">Xoá</button>
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

{{-- Modals: Category & Brand --}}
@include('partials.ui.catalog.category-modal')
@include('partials.ui.catalog.brand-modal')
@include('partials.ui.confirm-modal')
@endsection
@push('scripts')
<script>
// Giữ tab qua URL
function getTabFromURL(){
  const u = new URL(location.href);
  const t = u.searchParams.get('tab');
  return ['category','brand'].includes(t) ? t : 'category';
}
function setTabInURL(tab, replace=false){
  const u = new URL(location.href);
  u.searchParams.set('tab', tab);
  replace ? history.replaceState(null,'',u) : history.pushState(null,'',u);
}
function showTab(tab){
  const trigger = document.querySelector(`[data-bs-target="#${tab}-pane"]`);
  if (!trigger) return;
  new bootstrap.Tab(trigger).show();
}

document.addEventListener('DOMContentLoaded', () => {
  // mở đúng tab theo URL (mặc định 'category')
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
// Helpers chung
function makeHiddenInputs(container, name, values){
  container.innerHTML = '';
  values.forEach(v => {
    const i = document.createElement('input');
    i.type='hidden'; i.name=name; i.value=v; container.appendChild(i);
  });
}
function getCheckedValues(tableSel){
  const cbs = document.querySelectorAll(`${tableSel} tbody input[type=checkbox]:checked`);
  return Array.from(cbs).map(x=>x.value);
}
function toggleMaster(masterSel, rowSel){
  const master = document.querySelector(masterSel);
  const rows = document.querySelectorAll(rowSel);
  master?.addEventListener('change', ()=>{
    rows.forEach(cb => { cb.checked = master.checked; });
    master.indeterminate = false;
    updateBulkButtons();
  });
}
function updateBulkButtons(){
  const catAny = getCheckedValues('#categoryTable').length>0;
  const brandAny = getCheckedValues('#brandTable').length>0;
  document.getElementById('catBtnBulkDelete').disabled = !catAny;
  document.getElementById('brandBtnBulkDelete').disabled = !brandAny;
}

document.addEventListener('DOMContentLoaded', function(){
  // master checkbox binding
  toggleMaster('#cat_check_all',   '#categoryTable tbody .cat-row-checkbox');
  toggleMaster('#brand_check_all', '#brandTable tbody .brand-row-checkbox');

  document.querySelector('#categoryTable')?.addEventListener('change', (e)=>{
    if (e.target.classList?.contains('cat-row-checkbox')) updateBulkButtons();
  });
  document.querySelector('#brandTable')?.addEventListener('change', (e)=>{
    if (e.target.classList?.contains('brand-row-checkbox')) updateBulkButtons();
  });
  updateBulkButtons();

  // Bulk delete Category
  document.getElementById('catBtnBulkDelete')?.addEventListener('click', async ()=>{
    const ids = getCheckedValues('#categoryTable');
    if (!ids.length) return;
    const ok = await (window.UIConfirm? UIConfirm({title:'Xác nhận xoá', message:`Bạn sắp xoá <b>${ids.length}</b> category.`}) : Promise.resolve(confirm('Xoá các category đã chọn?')));
    if (!ok) return;
    const form = document.getElementById('catBulkForm');
    const box  = document.getElementById('catBulkIds');
    makeHiddenInputs(box, 'ids[]', ids);
    form.submit();
  });

  // Bulk delete Brand
  document.getElementById('brandBtnBulkDelete')?.addEventListener('click', async ()=>{
    const ids = getCheckedValues('#brandTable');
    if (!ids.length) return;
    const ok = await (window.UIConfirm? UIConfirm({title:'Xác nhận xoá', message:`Bạn sắp xoá <b>${ids.length}</b> brand.`}) : Promise.resolve(confirm('Xoá các brand đã chọn?')));
    if (!ok) return;
    const form = document.getElementById('brandBulkForm');
    const box  = document.getElementById('brandBulkIds');
    makeHiddenInputs(box, 'ids[]', ids);
    form.submit();
  });

  // ====== CATEGORY MODAL logic ======
  const catModal = document.getElementById('uiCategoryModal');
  const catForm  = document.getElementById('uiCategoryForm');
  const catImg   = document.getElementById('cat_image');
  const catPrev  = document.getElementById('cat_image_preview');
  const catPick  = document.getElementById('btnPickCatImage');

  document.querySelectorAll('.btnCateEdit').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      catForm.action = btn.dataset.updateUrl;
      catForm.querySelector('[name=_method]').value = 'PUT';
      catForm.querySelector('#cat_name').value = btn.dataset.name || '';
      catForm.querySelector('#cat_slug').value = btn.dataset.slug || '';
      catForm.querySelector('#cat_description').value = btn.dataset.description || '';
      catForm.querySelector('#cat_status').value = (btn.dataset.status||'ACTIVE');
      if (window.jQuery && $.fn?.select2) $('#cat_status').trigger('change.select2');
      if (catPrev) catPrev.src = btn.dataset.image || '';
      try{ if(catImg) catImg.value=''; }catch(_){ }
      bootstrap.Modal.getOrCreateInstance(catModal).show();
    });
  });
  catImg?.addEventListener('change', ()=>{ const f=catImg.files?.[0]; if(f) catPrev.src = URL.createObjectURL(f); });

  // ====== BRAND MODAL logic ======
  const brandModal = document.getElementById('uiBrandModal');
  const brandForm  = document.getElementById('uiBrandForm');
  const brandImg   = document.getElementById('brand_image');
  const brandPrev  = document.getElementById('brand_image_preview');
  const brandPick  = document.getElementById('btnPickBrandImage');

  document.querySelectorAll('.btnBrandEdit').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      brandForm.action = btn.dataset.updateUrl;
      brandForm.querySelector('[name=_method]').value = 'PUT';
      brandForm.querySelector('#brand_name').value = btn.dataset.name || '';
      brandForm.querySelector('#brand_slug').value = btn.dataset.slug || '';
      brandForm.querySelector('#brand_description').value = btn.dataset.description || '';
      brandForm.querySelector('#brand_status').value = (btn.dataset.status||'ACTIVE');
      if (window.jQuery && $.fn?.select2) $('#brand_status').trigger('change.select2');
      if (brandPrev) brandPrev.src = btn.dataset.image || '';
      try{ if(brandImg) brandImg.value=''; }catch(_){ }
      bootstrap.Modal.getOrCreateInstance(brandModal).show();
    });
  });
  brandImg?.addEventListener('change', ()=>{ const f=brandImg.files?.[0]; if(f) brandPrev.src = URL.createObjectURL(f); });
});
</script>
@endpush
