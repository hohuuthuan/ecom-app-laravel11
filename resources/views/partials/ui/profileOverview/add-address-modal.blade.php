<div
  class="modal fade"
  id="addAddressModal"
  tabindex="-1"
  aria-labelledby="addAddressModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="#">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addAddressModalLabel">
            Thêm địa chỉ giao hàng
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>

        <div class="modal-body">
          {{-- Địa chỉ giao hàng --}}
          <div class="mb-3">
            <label for="shippingAddress" class="form-label">
              Địa chỉ giao hàng <span class="text-danger">*</span>
            </label>
            <input
              type="text"
              id="shippingAddress"
              name="address"
              class="form-control"
              placeholder="VD: số 123, tổ 1, ấp Tân Hòa,..."
              value="{{ old('address') }}"
              required>
          </div>

          {{-- Tỉnh/Thành phố --}}
          <div class="mb-3">
            <label for="shippingProvince" class="form-label">
              Tỉnh/Thành phố <span class="text-danger">*</span>
            </label>
            <select
              id="shippingProvince"
              name="address_province_id"
              class="setupSelect2"
              data-wards-url="{{ route('user.profile.wards') }}"
              required>
              <option value="">Chọn Tỉnh/Thành phố</option>
              @foreach ($provinces as $province)
              <option
                value="{{ $province->id }}"
                @if (old('address_province_id')==$province->id) selected @endif>
                {{ $province->name }}
              </option>
              @endforeach
            </select>
          </div>

          {{-- Phường/Xã --}}
          <div class="mb-0">
            <label for="shippingWard" class="form-label">
              Phường/Xã <span class="text-danger">*</span>
            </label>
            <select
              id="shippingWard"
              name="address_ward_id"
              class="setupSelect2"
              required>
              <option value="">Chọn Phường/Xã</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-secondary"
            data-bs-dismiss="modal">
            Hủy
          </button>
          <button
            type="submit"
            class="btn btn-primary">
            Lưu địa chỉ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>