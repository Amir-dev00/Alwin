@extends('admin.layout')
@section('title', 'محصولات')
@section('content')
  <div class="page-head fly-item">
    <div>
      <p class="page-kicker">کاتالوگ</p>
      <h1>محصولات</h1>
      <p>پنجره‌ها، درب‌ها و توری‌ها با جفت تصویر باز و بسته.</p>
    </div>
    <div class="page-head-actions">
      <a class="btn btn-primary" href="{{ route('admin.products.create') }}">+ محصول جدید</a>
    </div>
  </div>

  <form class="filters filters-bar fly-item" method="get" style="--i:1">
    <div class="filters-main">
      <input type="search" name="search" value="{{ request('search') }}" placeholder="جستجوی نام یا نامک…">
      <select name="status">
        <option value="">همه وضعیت‌ها</option>
        <option value="published" @selected(request('status')==='published')>منتشر</option>
        <option value="draft" @selected(request('status')==='draft')>پیش‌نویس</option>
      </select>
      <select name="category_id">
        <option value="">همه دسته‌ها</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
      <select name="listing">
        <option value="">نمایش در صفحه محصولات</option>
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
          <th>نام</th>
          <th>دسته</th>
          <th>وضعیت</th>
          <th>خانه</th>
          <th>صفحه محصولات</th>
          <th class="col-actions"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td class="col-thumb">
              @if($item->imageClose)
                <img class="thumb" src="{{ $item->imageClose->thumbnailUrl() }}" alt="">
              @else
                <span class="thumb thumb-empty" aria-hidden="true"></span>
              @endif
            </td>
            <td>
              <strong>{{ $item->name }}</strong>
              <small>{{ $item->slug }}</small>
            </td>
            <td><span class="cell-muted">{{ $item->category?->name ?: '—' }}</span></td>
            <td><span class="pill {{ $item->status }}">{{ $item->status === 'published' ? 'منتشر' : 'پیش‌نویس' }}</span></td>
            <td>
              @if($item->show_on_home)
                <span class="pill published">خانه</span>
              @else
                <span class="cell-muted">—</span>
              @endif
            </td>
            <td>
              @if($item->trashed())
                <span class="cell-muted">—</span>
              @else
                <form method="post" action="{{ route('admin.products.listing', $item) }}">
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
                <form method="post" action="{{ route('admin.products.restore', $item->id) }}">@csrf<button class="btn btn-ghost btn-sm">بازیابی</button></form>
              @else
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.products.edit', $item) }}">ویرایش</a>
                <form method="post" action="{{ route('admin.products.duplicate', $item) }}">@csrf<button class="btn btn-quiet btn-sm">کپی</button></form>
                <form method="post" action="{{ route('admin.products.destroy', $item) }}" data-confirm="این محصول از سایت برداشته می‌شود و به سطل زباله می‌رود. ادامه می‌دهید؟">@csrf @method('delete')<button class="btn btn-danger btn-sm">حذف</button></form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="empty empty-soft">
                <strong>محصولی یافت نشد</strong>
                <span>فیلتر را عوض کنید یا محصول تازه‌ای بسازید.</span>
                <a class="btn btn-primary btn-sm" href="{{ route('admin.products.create') }}">محصول جدید</a>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links() }}
@endsection
