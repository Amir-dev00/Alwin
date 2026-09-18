@extends('admin.layout')
@section('title', 'مقالات')
@section('content')
  <div class="page-head fly-item">
    <div>
      <p class="page-kicker">محتوا</p>
      <h1>مقالات</h1>
      <p>مقالات تخصصی سایت. از اینجا انتخاب کنید کدام‌ها در صفحه مقالات دیده شوند.</p>
    </div>
    <div class="page-head-actions">
      <a class="btn btn-primary" href="{{ route('admin.articles.create') }}">+ مقاله جدید</a>
    </div>
  </div>

  <form class="filters filters-bar fly-item" method="get" style="--i:1">
    <div class="filters-main">
      <input type="search" name="search" value="{{ request('search') }}" placeholder="جستجوی عنوان…">
      <select name="status">
        <option value="">همه وضعیت‌ها</option>
        <option value="published" @selected(request('status')==='published')>منتشر</option>
        <option value="draft" @selected(request('status')==='draft')>پیش‌نویس</option>
      </select>
      <select name="listing">
        <option value="">نمایش در صفحه مقالات</option>
        <option value="1" @selected(request('listing')==='1')>در فهرست</option>
        <option value="0" @selected(request('listing')==='0')>خارج از فهرست</option>
      </select>
      <label class="inline chip-check"><input type="checkbox" name="trashed" value="1" @checked(request('trashed'))> سطل زباله</label>
    </div>
    <button class="btn btn-ghost" type="submit">اعمال فیلتر</button>
  </form>

  <div class="table-wrap fly-item" style="--i:2">
    <table class="data-table">
      <thead>
        <tr>
          <th class="col-thumb"></th>
          <th>عنوان</th>
          <th>انتشار</th>
          <th>وضعیت</th>
          <th>صفحه مقالات</th>
          <th class="col-actions"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td class="col-thumb">
              @if($item->coverThumbUrl() || $item->coverUrl())
                <img class="thumb" src="{{ $item->coverThumbUrl() ?: $item->coverUrl() }}" alt="">
              @else
                <span class="thumb thumb-empty" aria-hidden="true"></span>
              @endif
            </td>
            <td>
              <strong>{{ $item->title }}</strong>
              <small>{{ $item->slug }}</small>
            </td>
            <td><span class="cell-muted">{{ optional($item->published_at)?->format('Y-m-d') ?: '—' }}</span></td>
            <td><span class="pill {{ $item->status }}">{{ $item->status === 'published' ? 'منتشر' : 'پیش‌نویس' }}</span></td>
            <td>
              @if($item->trashed())
                <span class="cell-muted">—</span>
              @else
                <form method="post" action="{{ route('admin.articles.listing', $item) }}">
                  @csrf
                  <input type="hidden" name="show_on_listing" value="{{ $item->show_on_listing ? '0' : '1' }}">
                  <button class="btn btn-ghost btn-sm" type="submit">
                    @if($item->show_on_listing)
                      <span class="pill published">در فهرست</span>
                    @else
                      افزودن به فهرست
                    @endif
                  </button>
                </form>
              @endif
            </td>
            <td class="actions col-actions">
              @if($item->trashed())
                <form method="post" action="{{ route('admin.articles.restore', $item->id) }}">@csrf<button class="btn btn-ghost btn-sm">بازیابی</button></form>
              @else
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.articles.edit', $item) }}">ویرایش</a>
                <form method="post" action="{{ route('admin.articles.destroy', $item) }}" data-confirm="این مقاله از سایت برداشته می‌شود. ادامه می‌دهید؟">@csrf @method('delete')<button class="btn btn-danger btn-sm">حذف</button></form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6">
              <div class="empty empty-soft">
                <strong>مقاله‌ای یافت نشد</strong>
                <a class="btn btn-primary btn-sm" href="{{ route('admin.articles.create') }}">مقاله جدید</a>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links() }}
@endsection
