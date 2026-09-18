@if ($paginator->hasPages())
  <nav class="articles-pagination" role="navigation" aria-label="صفحه‌بندی" style="margin-top:2.5rem;display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap">
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
            <span aria-current="page"><strong>{{ $page }}</strong></span>
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
