<div class="modal fade" id="loginRequiredModal" tabindex="-1"
  aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginRequiredModalLabel">Cần đăng nhập</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        Bạn cần đăng nhập để sử dụng chức năng yêu thích sản phẩm
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Tôi biết rồi
        </button>
        <a href="{{ route('login.form') }}" class="btn btn-primary">
          Đăng nhập
        </a>
      </div>
    </div>
  </div>
</div>
