@extends('layouts.admin')

@section('title','Catalog: Category & author')

@section('body_class','catalog-page admin-catalog-page')

@section('content')
@php
$tab = request('tab');
$tab = in_array($tab, ['category', 'author', 'publisher'], true) ? $tab : 'category';
@endphp

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item breadcrumb-active" aria-current="page">Danh mục & Tác giả & Nhà xuất bản</li>
  </ol>
</nav>

<div class="admin-catalog-layout">
  <div class="admin-catalog-main">
    <div class="tab-content" id="catalogTabsContent">
      {{-- ================== PANE 1: CATEGORY ================== --}}
      <div class="tab-pane fade {{ $tab === 'category' ? 'show active' : '' }}" id="category-pane" role="region" aria-label="Danh mục">
        <div class="table-in-clip">
          <div class="card shadow-sm">
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
                <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
                <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
              </form>
            </div>

            <div class="card-body">
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
                  <button type="submit" class="btn-admin">Lọc</button>
                </div>
                <div class="col-md-1 d-grid">
                  @php($catClearQuery = \Illuminate\Support\Arr::except(request()->query(), ['cat_keyword','cat_status','page']))
                  @php($catClearQuery['tab'] = 'category')
                  @php($catClearQuery['per_page_cat'] = request('per_page_cat', 10))
                  <a href="{{ url()->current() . '?' . http_build_query($catClearQuery) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-eraser me-1"></i>Xóa lọc
                  </a>
                </div>
                <input type="hidden" name="tab" value="category">
                <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
              </form>

              <div class="d-flex justify-content-between mb-2">
                <div class="d-flex gap-2">
                  <button type="button" class="btn-admin" id="catBtnBulkDelete" disabled>Xoá đã chọn</button>
                </div>
                <div class="d-flex gap-2">
                  <button type="button" class="btn-admin" data-bs-toggle="modal" data-bs-target="#uiCategoryModal"><i class="fa fa-plus me-1"></i>Thêm danh mục</button>
                </div>
              </div>

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
                      <th class="actionWidth text-center">Thao tác</th>
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
                          data-status="{{ $cat->status }}">
                          <i class="fa fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" class="d-inline catDeleteForm">
                          @csrf
                          @method('DELETE')
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

      {{-- ================== PANE 2: AUTHOR ================== --}}
      <div class="tab-pane fade {{ $tab === 'author' ? 'show active' : '' }}" id="author-pane" role="region" aria-label="Tác giả">
        <div class="table-in-clip">
          <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Danh sách tác giả</h5>
              <form method="GET" class="d-flex align-items-center">
                <label class="me-2 mb-0">Hiển thị</label>
                <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_author" onchange="this.form.submit()">
                  <option value="10" {{ request('per_page_author')==10?'selected':'' }}>10</option>
                  <option value="20" {{ request('per_page_author')==20?'selected':'' }}>20</option>
                  <option value="50" {{ request('per_page_author')==50?'selected':'' }}>50</option>
                </select>
                <input type="hidden" name="tab" value="author">
                <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
                <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
                <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
              </form>
            </div>

            <div class="card-body">
              <form method="GET" class="row g-2 mb-3 filter-form">
                <div class="col-md-4">
                  <input type="text" name="author_keyword" class="form-control" placeholder="Tìm tên / slug" value="{{ request('author_keyword') }}">
                </div>
                <div class="col-md-2 select2CustomWidth">
                  <select name="author_status" class="form-select setupSelect2">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="ACTIVE" {{ request('author_status')==='ACTIVE'?'selected':'' }}>Đang hoạt động</option>
                    <option value="INACTIVE" {{ request('author_status')==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
                  </select>
                </div>
                <div class="col-md-1 d-grid">
                  <button type="submit" class="btn-admin">Lọc</button>
                </div>
                <div class="col-md-1 d-grid">
                  @php($authorClearQuery = \Illuminate\Support\Arr::except(request()->query(), ['author_keyword','author_status','page']))
                  @php($authorClearQuery['tab'] = 'author')
                  @php($authorClearQuery['per_page_author'] = request('per_page_author', 10))
                  <a href="{{ url()->current() . '?' . http_build_query($authorClearQuery) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-eraser me-1"></i>Xóa lọc
                  </a>
                </div>
                <input type="hidden" name="tab" value="author">
                <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
                <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
                <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
              </form>

              <div class="d-flex justify-content-between mb-2">
                <div class="d-flex gap-2">
                  <button type="button" class="btn-admin" id="authorBtnBulkDelete" disabled>Xoá đã chọn</button>
                </div>
                <div class="d-flex gap-2">
                  <button type="button" class="btn-admin" data-bs-toggle="modal" data-bs-target="#uiAuthorModal"><i class="fa fa-plus me-1"></i>Thêm tác giả</button>
                </div>
              </div>

              <div class="table-responsive">
                <table id="authorTable" class="table table-bordered table-striped align-middle">
                  <thead class="table-light">
                    <tr>
                      <th class="checkAllWidth"><input type="checkbox" id="author_check_all"></th>
                      <th class="STT_Width">#</th>
                      <th>Tên</th>
                      <th>Slug</th>
                      <th class="statusWidth">Trạng thái</th>
                      <th class="createTimeWidth">Tạo lúc</th>
                      <th class="actionWidth text-center">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($authors as $index => $author)
                    <tr>
                      <td><input type="checkbox" class="author-row-checkbox" value="{{ $author->id }}"></td>
                      <td>{{ $authors->firstItem() + $index }}</td>
                      <td>{{ $author->name }}</td>
                      <td>{{ $author->slug }}</td>
                      <td>
                        @if($author->status === 'ACTIVE')
                        <span class="badge bg-success">Đang hoạt động</span>
                        @else
                        <span class="badge bg-secondary">Ngừng hoạt động</span>
                        @endif
                      </td>
                      <td>{{ $author->created_at?->format('d/m/Y H:i') }}</td>
                      <td class="text-center">
                        <button type="button"
                          class="btn btn-sm btn-success btnauthorEdit"
                          data-update-url="{{ route('admin.authors.update', $author->id) }}"
                          data-name="{{ $author->name }}"
                          data-slug="{{ $author->slug }}"
                          data-description="{{ $author->description }}"
                          data-status="{{ $author->status }}"
                          data-image="{{ $author->image ? Storage::url('authors/'.$author->image) : '' }}">
                          <i class="fa fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.authors.destroy', $author->id) }}" class="d-inline authorDeleteForm">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger btnauthorDelete">
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
                {{ $authors->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ================== PANE 3: PUBLISHER ================== --}}
      <div class="tab-pane fade {{ $tab === 'publisher' ? 'show active' : '' }}" id="publisher-pane" role="region" aria-label="Nhà xuất bản">
        <div class="table-in-clip">
          <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Danh sách NXB</h5>
              <form method="GET" class="d-flex align-items-center">
                <label class="me-2 mb-0">Hiển thị</label>
                <select class="form-select form-select-sm w-auto setupSelect2" name="per_page_publisher" onchange="this.form.submit()">
                  <option value="10" {{ request('per_page_publisher')==10?'selected':'' }}>10</option>
                  <option value="20" {{ request('per_page_publisher')==20?'selected':'' }}>20</option>
                  <option value="50" {{ request('per_page_publisher')==50?'selected':'' }}>50</option>
                </select>
                <input type="hidden" name="tab" value="publisher">
                <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
                <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
                <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
                <input type="hidden" name="author_keyword" value="{{ request('author_keyword') }}">
                <input type="hidden" name="author_status" value="{{ request('author_status') }}">
                <input type="hidden" name="per_page_author" value="{{ request('per_page_author') }}">
              </form>
            </div>

            <div class="card-body">
              <form method="GET" class="row g-2 mb-3 filter-form">
                <div class="col-md-4">
                  <input type="text" name="publisher_keyword" class="form-control" placeholder="Tìm tên / slug" value="{{ request('publisher_keyword') }}">
                </div>
                <div class="col-md-2 select2CustomWidth">
                  <select name="publisher_status" class="form-select setupSelect2">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="ACTIVE" {{ request('publisher_status')==='ACTIVE'?'selected':'' }}>Đang hoạt động</option>
                    <option value="INACTIVE" {{ request('publisher_status')==='INACTIVE'?'selected':'' }}>Ngừng hoạt động</option>
                  </select>
                </div>
                <div class="col-md-1 d-grid">
                  <button type="submit" class="btn-admin">Lọc</button>
                </div>
                <div class="col-md-1 d-grid">
                  @php($publisherClearQuery = \Illuminate\Support\Arr::except(request()->query(), ['publisher_keyword','publisher_status','page']))
                  @php($publisherClearQuery['tab'] = 'publisher')
                  @php($publisherClearQuery['per_page_publisher'] = request('per_page_publisher', 10))
                  <a href="{{ url()->current() . '?' . http_build_query($publisherClearQuery) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-eraser me-1"></i>Xóa lọc
                  </a>
                </div>
                <input type="hidden" name="publisher_keyword" value="{{ request('publisher_keyword') }}">
                <input type="hidden" name="publisher_status" value="{{ request('publisher_status') }}">
                <input type="hidden" name="tab" value="publisher">
                <input type="hidden" name="cat_keyword" value="{{ request('cat_keyword') }}">
                <input type="hidden" name="cat_status" value="{{ request('cat_status') }}">
                <input type="hidden" name="per_page_cat" value="{{ request('per_page_cat') }}">
                <input type="hidden" name="author_keyword" value="{{ request('author_keyword') }}">
                <input type="hidden" name="author_status" value="{{ request('author_status') }}">
                <input type="hidden" name="per_page_author" value="{{ request('per_page_author') }}">
                <input type="hidden" name="per_page_publisher" value="{{ request('per_page_publisher') }}">
              </form>

              <div class="d-flex justify-content-between mb-2">
                <div class="d-flex gap-2">
                  <button type="button" class="btn-admin" id="publisherBtnBulkDelete" disabled>Xoá đã chọn</button>
                </div>
                <div class="d-flex gap-2">
                  <button type="button" class="btn-admin" data-bs-toggle="modal" data-bs-target="#uiPublisherModal"><i class="fa fa-plus me-1"></i>Thêm nhà xuất bản</button>
                </div>
              </div>

              <div class="table-responsive">
                <table id="publisherTable" class="table table-bordered table-striped align-middle">
                  <thead class="table-light">
                    <tr>
                      <th class="checkAllWidth"><input type="checkbox" id="publisher_check_all"></th>
                      <th class="STT_Width">#</th>
                      <th>Tên</th>
                      <th>Slug</th>
                      <th class="statusWidth">Trạng thái</th>
                      <th class="createTimeWidth">Tạo lúc</th>
                      <th class="actionWidth text-center">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($publishers as $index => $publisher)
                    <tr>
                      <td><input type="checkbox" class="publisher-row-checkbox" value="{{ $publisher->id }}"></td>
                      <td>{{ $publishers->firstItem() + $index }}</td>
                      <td>{{ $publisher->name }}</td>
                      <td>{{ $publisher->slug }}</td>
                      <td>
                        @if($publisher->status === 'ACTIVE')
                        <span class="badge bg-success">Đang hoạt động</span>
                        @else
                        <span class="badge bg-secondary">Ngừng hoạt động</span>
                        @endif
                      </td>
                      <td>{{ $publisher->created_at?->format('d/m/Y H:i') }}</td>
                      <td class="text-center">
                        <button type="button"
                          class="btn btn-sm btn-success btnPublisherEdit"
                          data-update-url="{{ route('admin.publishers.update', $publisher->id) }}"
                          data-name="{{ $publisher->name }}"
                          data-slug="{{ $publisher->slug }}"
                          data-description="{{ $publisher->description }}"
                          data-status="{{ $publisher->status }}"
                          data-image="{{ $publisher->image ? Storage::url('publishers/'.$publisher->image) : '' }}">
                          <i class="fa fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.publishers.destroy', $publisher->id) }}" class="d-inline publisherDeleteForm">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger btnPublisherDelete">
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
                {{ $publishers->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <form id="catBulkForm" method="POST" action="{{ route('admin.categories.bulk-delete') }}" class="d-none">
      @csrf
      <div id="catBulkIds"></div>
    </form>
    <form id="authorBulkForm" method="POST" action="{{ route('admin.authors.bulk-delete') }}" class="d-none">
      @csrf
      <div id="authorBulkIds"></div>
    </form>
    <form id="publisherBulkForm" method="POST" action="{{ route('admin.publishers.bulk-delete') }}" class="d-none">
      @csrf
      <div id="publisherBulkIds"></div>
    </form>

    <div id="__formState"
      data-has-errors="{{ $errors->any()?1:0 }}"
      data-which="{{ old('__form','') }}"
      data-mode="{{ old('__mode','create') }}"
      data-update-action="{{ old('__update_action','') }}"
      data-image="{{ old('__image','') }}"
      style="display:none"></div>

    @include('partials.ui.catalog.category-modal')
    @include('partials.ui.catalog.author-modal')
    @include('partials.ui.catalog.publisher-modal')
    @include('partials.ui.confirm-modal')
  </div>

  <aside class="admin-catalog-nav" aria-label="Catalog navigation">
    <div class="admin-catalog-nav-title">
      <i class="fa-solid fa-compass"></i>
      Điều hướng
    </div>

    <ul class="admin-catalog-nav-list">
      <li class="admin-catalog-nav-item">
        <a
          class="admin-catalog-nav-link {{ $tab === 'category' ? 'active' : '' }}"
          data-catalog-nav="category"
          href="{{ request()->fullUrlWithQuery(['tab' => 'category']) }}">
          <i class="fa-solid fa-layer-group"></i>
          <span class="nav-text">
            <span class="nav-text-main">Danh mục</span>
            <span class="nav-text-sub">Quản lý category</span>
          </span>
        </a>
      </li>

      <li class="admin-catalog-nav-item">
        <a
          class="admin-catalog-nav-link {{ $tab === 'author' ? 'active' : '' }}"
          data-catalog-nav="author"
          href="{{ request()->fullUrlWithQuery(['tab' => 'author']) }}">
          <i class="fa-solid fa-user-pen"></i>
          <span class="nav-text">
            <span class="nav-text-main">Tác giả</span>
            <span class="nav-text-sub">Quản lý author</span>
          </span>
        </a>
      </li>

      <li class="admin-catalog-nav-item">
        <a
          class="admin-catalog-nav-link {{ $tab === 'publisher' ? 'active' : '' }}"
          data-catalog-nav="publisher"
          href="{{ request()->fullUrlWithQuery(['tab' => 'publisher']) }}">
          <i class="fa-solid fa-building"></i>
          <span class="nav-text">
            <span class="nav-text-main">Nhà xuất bản</span>
            <span class="nav-text-sub">Quản lý publisher</span>
          </span>
        </a>
      </li>
    </ul>
  </aside>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/ecom-app-laravel_admin_catalog_index.js')
@endpush