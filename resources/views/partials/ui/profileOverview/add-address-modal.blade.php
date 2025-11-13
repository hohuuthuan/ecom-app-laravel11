<!-- Modal thêm địa chỉ giao hàng -->
<div
  class="modal fade"
  id="addAddressModal"
  tabindex="-1"
  aria-labelledby="addAddressModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content address-modal">
      <form method="POST" action="{{ route('user.profile.storeNewAddress') }}">
        @csrf
        @php
          $hasAnyAddress = isset($addresses) && count($addresses) > 0;
          $hasStoreError = isset($errors) && $errors->hasBag('addressStore') && $errors->addressStore->any();

          // Chỉ fill old() khi chính form thêm địa chỉ bị lỗi
          $oldAddress       = $hasStoreError ? old('address') : '';
          $oldProvinceId    = $hasStoreError ? old('address_province_id') : '';
          $oldWardId        = $hasStoreError ? old('address_ward_id') : '';
          $oldNote          = $hasStoreError ? old('note') : '';

          if ($hasStoreError) {
            $defaultChecked = old('default') === '1';
          } else {
            $defaultChecked = !$hasAnyAddress;
          }
        @endphp

        <div class="modal-header address-modal-header">
          <h5 class="modal-title" id="addAddressModalLabel">
            Thêm địa chỉ giao hàng
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
              <label for="shippingAddress" class="form-label address-label">
                Địa chỉ giao hàng <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                id="shippingAddress"
                name="address"
                class="form-control address-input @error('address','addressStore') is-invalid @enderror"
                placeholder="VD: số 123, tổ 1, ấp Tân Hòa,..."
                value="{{ $oldAddress }}">
              @error('address','addressStore')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
            </div>

            {{-- Tỉnh/Thành phố --}}
            <div class="col-md-6">
              <label for="shippingProvince" class="form-label address-label">
                Tỉnh/Thành phố <span class="text-danger">*</span>
              </label>
              <select
                id="shippingProvince"
                name="address_province_id"
                class="form-select address-select setupSelect2 @error('address_province_id','addressStore') is-invalid @enderror"
                data-wards-url="{{ route('user.profile.wards') }}">
                <option value="">Chọn Tỉnh/Thành phố</option>
                @foreach ($provinces as $province)
                  <option
                    value="{{ $province->id }}"
                    @if ($oldProvinceId == $province->id) selected @endif>
                    {{ $province->name }}
                  </option>
                @endforeach
              </select>
              @error('address_province_id','addressStore')
              <div class="invalid-feedback d-block">
                {{ $message }}
              </div>
              @enderror
            </div>

            {{-- Phường/Xã --}}
            <div class="col-md-6">
              <label for="shippingWard" class="form-label address-label">
                Phường/Xã <span class="text-danger">*</span>
              </label>
              <select
                id="shippingWard"
                name="address_ward_id"
                class="form-select address-select setupSelect2 @error('address_ward_id','addressStore') is-invalid @enderror"
                data-selected="{{ $oldWardId }}">
                <option value="">Chọn Phường/Xã</option>
                {{-- JS sẽ fill ward dựa theo province --}}
              </select>
              @error('address_ward_id','addressStore')
              <div class="invalid-feedback d-block">
                {{ $message }}
              </div>
              @enderror
            </div>

            {{-- Ghi chú --}}
            <div class="col-12">
              <label for="addressNote" class="form-label address-label">
                Ghi chú (không bắt buộc)
              </label>
              <textarea
                id="addressNote"
                name="note"
                class="form-control address-input @error('note','addressStore') is-invalid @enderror"
                rows="2"
                placeholder="VD: Giao giờ hành chính, gọi trước khi giao">{{ $oldNote }}</textarea>
              @error('note','addressStore')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          {{-- Đặt làm mặc định --}}
          <div class="mt-3 pt-3 border-top">
            <div class="form-check form-switch">
              <input type="hidden" name="default" value="0">
              <input
                class="form-check-input @error('default','addressStore') is-invalid @enderror"
                type="checkbox"
                role="switch"
                id="addressDefault"
                name="default"
                value="1"
                @if ($defaultChecked) checked @endif>
              <label class="form-check-label" for="addressDefault">
                Đặt làm địa chỉ mặc định
              </label>
              <div class="form-text">
                Nếu bật, địa chỉ này sẽ được dùng mặc định khi đặt hàng.
              </div>
              @error('default','addressStore')
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
            Lưu địa chỉ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
