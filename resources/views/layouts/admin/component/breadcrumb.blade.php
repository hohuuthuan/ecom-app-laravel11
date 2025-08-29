<ol class="breadcrumb" style="margin:10px 0">
  @foreach(($items ?? []) as $item)
    @if(!empty($item['url']))
      <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
    @else
      <li class="active">{{ $item['label'] }}</li>
    @endif
  @endforeach
</ol>
