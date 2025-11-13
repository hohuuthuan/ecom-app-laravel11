<!-- Modal cập nhật địa chỉ giao hàng -->
<div
  class="modal fade"
  id="updateAddressModal"
  tabindex="-1"
  aria-labelledby="updateAddressModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content address-modal">
      @php
        // id đang chỉnh sửa (khi có lỗi validate)
        $updateId = old('id', session('editing_address_id'));
      @endphp

      <form
        method="POST"
        action="{{ $updateId ? route('user.profile.updateAddress', $updateId) : '#' }}">
        @csrf
        @method('PUT')

        {{-- id address (phục vụ cho validate + fill lại khi lỗi) --}}
        <input type="hidden" name="id" id="updateAddressId" value="{{ $updateId }}">

        <div class="modal-header address-modal-header">
          <h5 class="modal-title" id="updateAddressModalLabel">
            Chỉnh sửa địa chỉ giao hàng
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>

        <div class="modal-body address-modal-body">
          <div class="row g-3">
            {{-- Địa chỉ giao hàng --}}
            <div class="col-12">
              <label for="updateShippingAddress" class="form-label address-label">
                Địa chỉ giao hàng <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                id="updateShippingAddress"
                name="address"
                class="form-control address-input @error('address','addressUpdate') is-invalid @enderror"
                placeholder="VD: số 123, tổ 1, ấp Tân Hòa,..."
                value="{{ old('address') }}">
              @error('address','addressUpdate')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
            </div>

            {{-- Tỉnh/Thành phố --}}
            <div class="col-md-6">
              <label for="updateShippingProvince" class="form-label address-label">
                Tỉnh/Thành phố <span class="text-danger">*</span>
              </label>
              <select
                id="updateShippingProvince"
                name="address_province_id"
                class="form-select address-select setupSelect2 @error('address_province_id','addressUpdate') is-invalid @enderror"
                data-wards-url="{{ route('user.profile.wards') }}">
                <option value="">Chọn Tỉnh/Thành phố</option>
                @foreach ($provinces as $province)
                  <option
                    value="{{ $province->id }}"
                    @if (old('address_province_id') == $province->id) selected @endif>
                    {{ $province->name }}
                  </option>
                @endforeach
              </select>
              @error('address_province_id','addressUpdate')
              <div class="invalid-feedback d-block">
                {{ $message }}
              </div>
              @enderror
            </div>

            {{-- Phường/Xã --}}
            <div class="col-md-6">
              <label for="updateShippingWard" class="form-label address-label">
                Phường/Xã <span class="text-danger">*</span>
              </label>
              <select
                id="updateShippingWard"
                name="address_ward_id"
                class="form-select address-select setupSelect2 @error('address_ward_id','addressUpdate') is-invalid @enderror"
                data-selected="{{ old('address_ward_id') }}">
                <option value="">Chọn Phường/Xã</option>
                {{-- option sẽ được JS fill / hoặc sau này nếu muốn có thể đổ sẵn từ controller khi lỗi --}}
              </select>
              @error('address_ward_id','addressUpdate')
              <div class="invalid-feedback d-block">
                {{ $message }}
              </div>
              @enderror
            </div>

            {{-- Ghi chú --}}
            <div class="col-12">
              <label for="updateAddressNote" class="form-label address-label">
                Ghi chú (không bắt buộc)
              </label>
              <textarea
                id="updateAddressNote"
                name="note"
                class="form-control address-input @error('note','addressUpdate') is-invalid @enderror"
                rows="2"
                placeholder="VD: Giao giờ hành chính, gọi trước khi giao">{{ old('note') }}</textarea>
              @error('note','addressUpdate')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          {{-- Đặt làm mặc định --}}
          <div class="mt-3 pt-3 border-top">
            <div class="form-check form-switch">
              {{-- đảm bảo luôn gửi default --}}
              <input type="hidden" name="default" value="0">
              <input
                class="form-check-input @error('default','addressUpdate') is-invalid @enderror"
                type="checkbox"
                role="switch"
                id="updateAddressDefault"
                name="default"
                value="1"
                @if (old('default') === '1') checked @endif>
              <label class="form-check-label" for="updateAddressDefault">
                Đặt làm địa chỉ mặc định
              </label>
              <div class="form-text">
                Nếu bật, địa chỉ này sẽ được dùng mặc định khi đặt hàng.
              </div>
              @error('default','addressUpdate')
              <div class="invalid-feedback d-block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>
        </div>

        <div class="modal-footer address-modal-footer">
          <button
            type="button"
            class="btn btn-outline-secondary rounded-pill px-4"
            data-bs-dismiss="modal">
            Hủy
          </button>
          <button
            type="submit"
            class="btn btn-primary rounded-pill px-4">
            Cập nhật địa chỉ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
