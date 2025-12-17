  <div
    class="modal fade"
    id="discountEditModal"
    tabindex="-1"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <form
        class="modal-content"
        id="discountEditForm"
        method="POST"
        action="">
        @csrf
        @method('PUT')

        @php
          $editErrors = $errors->discountEdit ?? $errors;
        @endphp

        <div class="modal-header">
          <h3 class="modal-title">Chỉnh sửa mã giảm giá</h3>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Đóng"></button>
        </div>

        <div class="modal-body">
          <input
            type="hidden"
            name="id"
            id="edit_discount_id"
            value="{{ old('id') }}">

          <div class="row g-3">
            {{-- Mã giảm giá --}}
            <div class="col-md-4">
              <label for="edit_discount_code" class="form-label">
                <b>Mã giảm giá</b> <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                id="edit_discount_code"
                name="code"
                class="form-control {{ $editErrors->has('code') ? 'is-invalid' : '' }}"
                value="{{ old('code') }}"
                maxlength="64"
                placeholder="Nhập mã giảm giá">
              <div
                class="invalid-feedback {{ $editErrors->has('code') ? 'd-block' : 'd-none' }}"
                data-err="code">
                {{ $editErrors->first('code') }}
              </div>
            </div>

            {{-- Loại mã --}}
            <div class="col-md-4 select2CustomWidth">
              <label for="edit_discount_type" class="form-label">
                <b>Loại mã</b> <span class="text-danger">*</span>
              </label>
              @php($t = old('type'))
              <select
                id="edit_discount_type"
                name="type"
                class="form-select setupSelect2 {{ $editErrors->has('type') ? 'is-invalid' : '' }}"
                data-placeholder="Chọn loại mã">
                <option value="percent" {{ $t==='percent' ? 'selected' : '' }}>Giảm theo %</option>
                <option value="fixed" {{ $t==='fixed' ? 'selected' : '' }}>Giảm trực tiếp (VNĐ)</option>
                <option value="shipping" {{ $t==='shipping' ? 'selected' : '' }}>Giảm phí vận chuyển</option>
              </select>
              <div
                class="invalid-feedback {{ $editErrors->has('type') ? 'd-block' : 'd-none' }}"
                data-err="type">
                {{ $editErrors->first('type') }}
              </div>
            </div>

            {{-- Giá trị --}}
            <div class="col-md-4">
              <label for="edit_discount_value" class="form-label">
                <b>Giá trị</b> <span class="text-danger">*</span>
              </label>
              <input
                type="number"
                id="edit_discount_value"
                name="value"
                class="form-control {{ $editErrors->has('value') ? 'is-invalid' : '' }}"
                value="{{ old('value') }}"
                min="1"
                placeholder="Nhập giá trị">
              <div
                class="invalid-feedback {{ $editErrors->has('value') ? 'd-block' : 'd-none' }}"
                data-err="value">
                {{ $editErrors->first('value') }}
              </div>
              <div class="form-text">
                Nếu là % thì từ 1 đến 100, nếu là VNĐ thì nhập số tiền.
              </div>
            </div>

            {{-- Đơn tối thiểu --}}
            <div class="col-md-4">
              <label for="edit_discount_min_order" class="form-label">
                <b>Đơn tối thiểu (VNĐ)</b>
              </label>
              <input
                type="number"
                id="edit_discount_min_order"
                name="min_order_value_vnd"
                class="form-control {{ $editErrors->has('min_order_value_vnd') ? 'is-invalid' : '' }}"
                value="{{ old('min_order_value_vnd') }}"
                min="0"
                placeholder="Không bắt buộc">
              <div
                class="invalid-feedback {{ $editErrors->has('min_order_value_vnd') ? 'd-block' : 'd-none' }}"
                data-err="min_order_value_vnd">
                {{ $editErrors->first('min_order_value_vnd') }}
              </div>
            </div>

            {{-- Giới hạn lượt dùng (tổng) --}}
            <div class="col-md-4">
              <label for="edit_discount_usage_limit" class="form-label">
                <b>Giới hạn lượt dùng (tổng)</b>
              </label>
              <input
                type="number"
                id="edit_discount_usage_limit"
                name="usage_limit"
                class="form-control {{ $editErrors->has('usage_limit') ? 'is-invalid' : '' }}"
                value="{{ old('usage_limit') }}"
                min="1"
                placeholder="Không bắt buộc">
              <div
                class="invalid-feedback {{ $editErrors->has('usage_limit') ? 'd-block' : 'd-none' }}"
                data-err="usage_limit">
                {{ $editErrors->first('usage_limit') }}
              </div>
              <div class="form-text">
                Để trống nếu không giới hạn tổng số lượt sử dụng.
              </div>
            </div>

            {{-- Giới hạn mỗi người dùng --}}
            <div class="col-md-4">
              <label for="edit_discount_per_user_limit" class="form-label">
                <b>Giới hạn mỗi người dùng</b>
              </label>
              <input
                type="number"
                id="edit_discount_per_user_limit"
                name="per_user_limit"
                class="form-control {{ $editErrors->has('per_user_limit') ? 'is-invalid' : '' }}"
                value="{{ old('per_user_limit') }}"
                min="1"
                placeholder="Không bắt buộc">
              <div
                class="invalid-feedback {{ $editErrors->has('per_user_limit') ? 'd-block' : 'd-none' }}"
                data-err="per_user_limit">
                {{ $editErrors->first('per_user_limit') }}
              </div>
              <div class="form-text">
                Để trống nếu không giới hạn số lần trên mỗi tài khoản.
              </div>
            </div>
            <div class="col-md-6">
              <label for="edit_discount_start" class="form-label">
                <b>Ngày bắt đầu</b>
              </label>
              <input
                type="datetime-local"
                id="edit_discount_start"
                name="start_date"
                class="form-control {{ $editErrors->has('start_date') ? 'is-invalid' : '' }}"
                value="{{ old('start_date') }}">
              <div
                class="invalid-feedback {{ $editErrors->has('start_date') ? 'd-block' : 'd-none' }}"
                data-err="start_date">
                {{ $editErrors->first('start_date') }}
              </div>
            </div>
            <div class="col-md-6">
              <label for="edit_discount_end" class="form-label">
                <b>Ngày kết thúc</b>
              </label>
              <input
                type="datetime-local"
                id="edit_discount_end"
                name="end_date"
                class="form-control {{ $editErrors->has('end_date') ? 'is-invalid' : '' }}"
                value="{{ old('end_date') }}">
              <div
                class="invalid-feedback {{ $editErrors->has('end_date') ? 'd-block' : 'd-none' }}"
                data-err="end_date">
                {{ $editErrors->first('end_date') }}
              </div>
            </div>
            <div class="col-md-4">
              <label for="edit_discount_status" class="form-label">
                <b>Trạng thái</b> <span class="text-danger">*</span>
              </label>
              @php($st = old('status','ACTIVE'))
              <select
                id="edit_discount_status"
                name="status"
                class="form-select setupSelect2 {{ $editErrors->has('status') ? 'is-invalid' : '' }}"
                data-placeholder="Chọn trạng thái">
                <option value="ACTIVE" {{ $st==='ACTIVE' ? 'selected' : '' }}>Phát hành</option>
                <option value="INACTIVE" {{ $st==='INACTIVE' ? 'selected' : '' }}>Ngừng phát hành</option>
              </select>
              <div
                class="invalid-feedback {{ $editErrors->has('status') ? 'd-block' : 'd-none' }}"
                data-err="status">
                {{ $editErrors->first('status') }}
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-secondary btn-cancel-modal"
            data-bs-dismiss="modal">
            Huỷ
          </button>
          <button
            type="submit"
            class="btn btn-success btn-submit-modal">
            Lưu
          </button>
        </div>
      </form>
    </div>
  </div>

  @php($hasEditErr = ($errors->discountEdit ?? $errors)->any())
  <div
    id="__discountEditState"
    data-has-errors="{{ $hasEditErr ? 1 : 0 }}"
    style="display:none"></div>
