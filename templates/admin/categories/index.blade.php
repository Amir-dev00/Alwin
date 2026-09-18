@extends('admin.layout')
@section('title', 'دسته‌ها')
@section('content')
  <div class="page-head">
    <div>
      <p class="page-kicker">کاتالوگ</p>
      <h1>دسته‌های محصول</h1>
      <p>مثلاً «درب و پنجره» و «توری». آدرس اینترنتی خودش ساخته می‌شود.</p>
    </div>
  </div>
  <div class="grid-2">
    <section class="card easy-card">
      <header><h2>دسته تازه</h2></header>
      <form method="post" action="{{ route('admin.categories.store') }}" class="stack">
        @csrf
        <label class="field">
          <span>نام دسته</span>
          <input name="name" required data-slug-source placeholder="مثلاً توری پنجره">
        </label>
        <input type="hidden" name="slug" data-slug-target>
        <input type="hidden" name="sort_order" value="{{ (int) ($items->max('sort_order') + 1) }}">
        <button class="btn btn-primary" type="submit">افزودن دسته</button>
      </form>
    </section>
    <section class="card easy-card">
      <header><h2>فهرست</h2></header>
      @forelse($items as $item)
        <form method="post" action="{{ route('admin.categories.update', $item) }}" class="inline-row">
          @csrf @method('put')
          <input name="name" value="{{ $item->name }}" required>
          <input type="hidden" name="slug" value="{{ $item->slug }}">
          <input type="number" name="sort_order" value="{{ $item->sort_order }}" class="w-sm" title="ترتیب">
          <label class="inline"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> نمایش</label>
          <button class="btn btn-ghost" type="submit">ذخیره</button>
        </form>
        <form method="post" action="{{ route('admin.categories.destroy', $item) }}" data-confirm="اگر محصولی در این دسته باشد حذف نمی‌شود. ادامه می‌دهید؟" class="inline-delete">
          @csrf @method('delete')
          <button class="btn btn-danger" type="submit">حذف</button>
        </form>
      @empty
        <div class="empty empty-soft">دسته‌ای ثبت نشده است.</div>
      @endforelse
    </section>
  </div>
@endsection
