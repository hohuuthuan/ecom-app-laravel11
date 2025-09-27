@extends('layouts.user')
@section('title','Trang chủ')

@section('content')
<main class="flex-grow-1" role="main">
    <!-- Hero banner -->
    <section class="py-3">
      <div class="container">
        <div class="hero ratio ratio-21x9 rounded-4 overflow-hidden">
          <img class="w-100 h-100 object-fit-cover" src="https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=1600&auto=format&fit=crop" alt="Banner giới thiệu LeafBook">
          <div class="hero-overlay p-4 p-md-5">
            <h1 class="display-6 text-white fw-bold mb-2">Đọc hay mỗi ngày</h1>
            <p class="lead text-white-50 mb-3">Ưu đãi độc quyền. Giao nhanh toàn quốc. Sách chính hãng.</p>
            <div class="d-flex gap-2">
              <a class="btn btn-brand" href="#featured">Khám phá ngay</a>
              <a class="btn btn-outline-brand" href="#">Xem khuyến mãi</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured products -->
    <section id="featured" class="py-4">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h4 m-0">Sản phẩm nổi bật</h2>
          <a class="link-brand" href="#">Xem tất cả</a>
        </div>

        <div class="row g-3 row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
          <!-- 10 demo items -->
          <!-- Item template repeated -->
          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-20%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf1/400/520" alt="Sách 1">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Đắc Nhân Tâm</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">180.000₫</span>
                    <span class="new">144.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-35%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf2/400/520" alt="Sách 2">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Atomic Habits</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">220.000₫</span>
                    <span class="new">143.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-15%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf3/400/520" alt="Sách 3">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Clean Code</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">320.000₫</span>
                    <span class="new">272.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-22%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf4/400/520" alt="Sách 4">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Sapiens</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">260.000₫</span>
                    <span class="new">202.800₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-12%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf5/400/520" alt="Sách 5">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">The Pragmatic Programmer</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">300.000₫</span>
                    <span class="new">264.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-18%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf6/400/520" alt="Sách 6">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Deep Work</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">240.000₫</span>
                    <span class="new">196.800₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-10%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf7/400/520" alt="Sách 7">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Zero to One</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">210.000₫</span>
                    <span class="new">189.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-25%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf8/400/520" alt="Sách 8">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Project Hail Mary</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">280.000₫</span>
                    <span class="new">210.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-30%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf9/400/520" alt="Sách 9">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">The Alchemist</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">190.000₫</span>
                    <span class="new">133.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>

          <div class="col">
            <article class="card product h-100">
              <div class="position-relative">
                <span class="discount">-14%</span>
                <img class="card-img-top" src="https://picsum.photos/seed/leaf10/400/520" alt="Sách 10">
              </div>
              <div class="card-body d-flex flex-column">
                <h3 class="product-title">Start With Why</h3>
                <div class="mt-auto">
                  <div class="price">
                    <span class="old">200.000₫</span>
                    <span class="new">172.000₫</span>
                  </div>
                  <button class="btn btn-brand w-100 mt-2 add-to-cart"><i class="bi bi-bag-plus me-1"></i>Thêm vào giỏ</button>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- New arrivals scroller -->
    <section class="py-4">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h4 m-0">Mới lên kệ</h2>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-brand btn-sm sc-prev" data-target="#newScroller"><i class="bi bi-chevron-left"></i></button>
            <button class="btn btn-outline-brand btn-sm sc-next" data-target="#newScroller"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
        <div id="newScroller" class="h-scroller">
          <!-- 8 items -->
          <div class="h-item card"><img src="https://picsum.photos/seed/n1/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">The Midnight Library</div></div>
          <div class="h-item card"><img src="https://picsum.photos/seed/n2/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">Sổ tay đọc sách</div></div>
          <div class="h-item card"><img src="https://picsum.photos/seed/n3/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">Làm chủ thói quen</div></div>
          <div class="h-item card"><img src="https://picsum.photos/seed/n4/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">Khoa học vui</div></div>
          <div class="h-item card"><img src="https://picsum.photos/seed/n5/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">Lập trình sạch</div></div>
          <div class="h-item card"><img src="https://picsum.photos/seed/n6/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">Tư duy nhanh chậm</div></div>
          <div class="h-item card"><img src="https://picsum.photos/seed/n7/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">Cha giàu cha nghèo</div></div>
          <div class="h-item card"><img src="https://picsum.photos/seed/n8/400/520" class="card-img-top" alt=""><div class="card-body py-2 small fw-medium text-truncate">Dế mèn phiêu lưu ký</div></div>
        </div>
      </div>
    </section>

    <!-- Posts scroller -->
    <section class="py-4">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h4 m-0">Bài viết</h2>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-brand btn-sm sc-prev" data-target="#postScroller"><i class="bi bi-chevron-left"></i></button>
            <button class="btn btn-outline-brand btn-sm sc-next" data-target="#postScroller"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
        <div id="postScroller" class="h-scroller">
          <article class="h-item card">
            <img src="https://picsum.photos/seed/p1/800/500" class="card-img-top" alt="">
            <div class="card-body">
              <h3 class="h6 text-truncate m-0">Top 10 sách kỹ năng</h3>
              <p class="small text-secondary text-truncate-2 m-0">Danh sách giúp tăng hiệu suất và cân bằng cuộc sống.</p>
            </div>
          </article>
          <article class="h-item card">
            <img src="https://picsum.photos/seed/p2/800/500" class="card-img-top" alt="">
            <div class="card-body">
              <h3 class="h6 text-truncate m-0">Chọn sách cho thiếu nhi</h3>
              <p class="small text-secondary text-truncate-2 m-0">Tiêu chí an toàn, giáo dục theo từng độ tuổi.</p>
            </div>
          </article>
          <article class="h-item card">
            <img src="https://picsum.photos/seed/p3/800/500" class="card-img-top" alt="">
            <div class="card-body">
              <h3 class="h6 text-truncate m-0">Bí quyết đọc hiệu quả</h3>
              <p class="small text-secondary text-truncate-2 m-0">Kỹ thuật ghi chú và tóm tắt nội dung.</p>
            </div>
          </article>
          <article class="h-item card">
            <img src="https://picsum.photos/seed/p4/800/500" class="card-img-top" alt="">
            <div class="card-body">
              <h3 class="h6 text-truncate m-0">Tủ sách khởi nghiệp</h3>
              <p class="small text-secondary text-truncate-2 m-0">Những tựa sách nền tảng cho người mới bắt đầu.</p>
            </div>
          </article>
          <article class="h-item card">
            <img src="https://picsum.photos/seed/p5/800/500" class="card-img-top" alt="">
            <div class="card-body">
              <h3 class="h6 text-truncate m-0">Văn học Việt đương đại</h3>
              <p class="small text-secondary text-truncate-2 m-0">Tác phẩm và tác giả tiêu biểu gần đây.</p>
            </div>
          </article>
          <article class="h-item card">
            <img src="https://picsum.photos/seed/p6/800/500" class="card-img-top" alt="">
            <div class="card-body">
              <h3 class="h6 text-truncate m-0">Sách khoa học phổ thông</h3>
              <p class="small text-secondary text-truncate-2 m-0">Nuôi dưỡng tư duy khám phá.</p>
            </div>
          </article>
        </div>
      </div>
    </section>
  </main>
@endsection
