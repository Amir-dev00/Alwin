@extends('admin.layout')
@section('title', 'منو')
@section('content')
  <div class="page-head">
    <div>
      <p class="page-kicker">سایت</p>
      <h1>منو و لینک‌ها</h1>
      <p>از فهرست صفحه را انتخاب کنید تا آدرس خودش نوشته شود. ترتیب را بکشید و ذخیره کنید.</p>
    </div>
  </div>
  <div class="grid-2">
    @foreach(['header' => $header, 'footer' => $footer] as $location => $items)
      <section class="card easy-card">
        <header><h2>{{ $location === 'header' ? 'منوی بالای سایت' : 'لینک‌های پایین سایت' }}</h2></header>
        <form method="post" action="{{ route('admin.navigation.store') }}" class="stack tight">
          @csrf
          <input type="hidden" name="location" value="{{ $location }}">
          <label class="field"><span>نام لینک</span><input name="label" required placeholder="مثلاً تماس با ما"></label>
          <label class="field">
            <span>کدام صفحه؟</span>
            <select data-nav-page>
              <option value="">انتخاب صفحه…</option>
              @foreach($pages as $page)
                <option value="{{ $page->path }}">{{ $page->title }}</option>
              @endforeach
              <option value="articles/">مقالات</option>
              <option value="custom">آدرس دلخواه</option>
            </select>
          </label>
          <label class="field">
            <span>آدرس</span>
            <input name="url" required placeholder="about.html" dir="ltr">
            <small class="field-help">اگر صفحه را از فهرست بالا بزنید، اینجا خودش پر می‌شود.</small>
          </label>
          <button class="btn btn-primary" type="submit">افزودن لینک</button>
        </form>
        <form method="post" action="{{ route('admin.navigation.reorder') }}" class="nav-list" data-sortable>
          @csrf
          @foreach($items as $item)
            <article class="nav-item" draggable="true" data-id="{{ $item->id }}">
              <input type="hidden" name="order[]" value="{{ $item->id }}">
              <span class="drag">⋮⋮</span>
              <div>
                <strong>{{ $item->label }}</strong>
                <small>{{ $item->url }}</small>
              </div>
            </article>
          @endforeach
          <button class="btn btn-ghost" type="submit">ذخیره ترتیب</button>
        </form>
        @foreach($items as $item)
          <form method="post" action="{{ route('admin.navigation.update', $item) }}" class="inline-row">
            @csrf @method('put')
            <input name="label" value="{{ $item->label }}" required>
            <input name="url" value="{{ $item->url }}" required dir="ltr">
            <label class="inline"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> نمایش</label>
            <button class="btn btn-ghost" type="submit">ذخیره</button>
          </form>
          <form method="post" action="{{ route('admin.navigation.destroy', $item) }}" data-confirm="این لینک از منو حذف شود؟" class="inline-delete">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit">حذف</button>
          </form>
        @endforeach
      </section>
    @endforeach
  </div>
@endsection
