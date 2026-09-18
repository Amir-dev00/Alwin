@if ($paginator->hasPages())
  <nav class="pagination" role="navigation" aria-label="صفحه‌بندی">
    @if ($paginator->onFirstPage())
      <span aria-disabled="true">قبلی</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}">قبلی</a>
    @endif
    @foreach ($elements as $element)
      @if (is_string($element))
        <span>{{ $element }}</span>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="active"><span>{{ $page }}</span></span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}">بعدی</a>
    @else
      <span aria-disabled="true">بعدی</span>
    @endif
  </nav>
@endif
