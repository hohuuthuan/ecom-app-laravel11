@if (isset($addresses) && $addresses->count() > 0)
<div
  class="modal fade"
  id="selectAddressModal"
  tabindex="-1"
  aria-labelledby="selectAddressModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="selectAddressModalLabel">
          Chọn địa chỉ giao hàng
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>

      <div class="modal-body">
        @foreach ($addresses as $address)
        @php
          $isActive = isset($selectedAddress) && $selectedAddress && $selectedAddress->id === $address->id;
          $fullText = $address->address;
          if ($address->ward || $address->province) {
            $fullText .= ', ' . optional($address->ward)->name . ', ' . optional($address->province)->name;
          }
        @endphp
        <button
          type="button"
          class="address-card address-select-card js-address-select-card w-100 text-start mb-2 {{ $isActive ? 'is-active' : '' }}"
          data-id="{{ $address->id }}"
          data-text="{{ $fullText }}"
          data-note="{{ $address->note ?? '' }}">
          <div class="address-card-header">
            <div class="address-card-title">
              <i class="bi bi-geo-alt"></i>
              <span>{{ $address->address }}</span>
              @if ($address->default)
              <span class="badge bg-primary ms-2">Mặc định</span>
              @endif
            </div>
          </div>
          <div class="address-card-body">
            {{ $address->address }}
            @if ($address->ward || $address->province)
            , {{ optional($address->ward)->name }},
            {{ optional($address->province)->name }}
            @endif
            @if (!empty($address->note))
            <br>
            <small class="text-muted">Ghi chú: {{ $address->note }}</small>
            @endif
          </div>
        </button>
        @endforeach
      </div>

      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal">
          Đóng
        </button>
      </div>
    </div>
  </div>
</div>
@endif
