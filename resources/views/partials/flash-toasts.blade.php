@if (session('success') || session('error') || session('warning') || session('info') || ($errors ?? null)?->any())
  <div class="tw-toast-container">
    @foreach (['success','error','warning','info'] as $type)
      @if (session($type))
        <div data-toast="{{ $type }}" data-autohide="true" data-delay-ms="3500">
          {{ session($type) }}
        </div>
      @endif
    @endforeach

    @if (($errors ?? null)?->any())
      @foreach (($errors->all() ?? []) as $msg)
        <div data-toast="error" data-autohide="false">
          {{ $msg }}
        </div>
      @endforeach
    @endif
  </div>
@endif
