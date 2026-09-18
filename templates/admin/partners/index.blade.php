@extends('admin.layout')
@section('title', 'مشتریان')
@section('content')
  <div class="page-head">
    <div>
      <h1>مشتریان و لوگوها</h1>
      <p>لوگوی نوار مشتریان صفحه خانه. نام شرکت را بنویسید، لوگو را عوض کنید و ترتیب را با شماره مشخص کنید.</p>
    </div>
  </div>
  <section class="card">
    <header><h2>افزودن لوگو</h2></header>
        <form method="post" action="{{ route('admin.partners.store') }}" class="stack" data-easy-form>
      @csrf
      <div class="inline-row">
        <input name="name" placeholder="نام شرکت" required data-fill-source="title">
        <input name="alt_text" placeholder="توضیح عکس (خودکار)" data-autofill="alt">
        <input name="url" placeholder="لینک سایت (اختیاری)" dir="ltr">
        <input type="hidden" name="sort_order" value="{{ (int) ($items->max('sort_order') + 1) }}">
        <button class="btn btn-primary" type="submit">افزودن</button>
      </div>
      @include('admin.partials.media-picker', [
        'name' => 'image_id',
        'label' => 'لوگوی همکار',
        'collection' => 'partners',
        'hint' => 'لوگو را اینجا رها کنید',
        'help' => 'ترجیحاً WebP شفاف یا پس‌زمینه روشن',
      ])
    </form>
  </section>
  <div class="media-grid">
    @forelse($items as $item)
      <article class="media-card">
        @if($item->image)
          <img src="{{ $item->image->thumbnailUrl() }}" alt="{{ $item->alt_text }}">
        @endif
        <form method="post" action="{{ route('admin.partners.update', $item) }}" class="stack tight">
          @csrf @method('put')
          <input name="name" value="{{ $item->name }}" required data-fill-source="title">
          <input name="alt_text" value="{{ $item->alt_text }}" data-autofill="alt" placeholder="توضیح عکس">
          @include('admin.partials.media-picker', [
            'name' => 'image_id',
            'label' => 'لوگو',
            'collection' => 'partners',
            'value' => $item->image_id,
            'preview' => $item->image?->publicUrl(),
            'filename' => $item->image?->original_name,
            'hint' => 'لوگو را تعویض کنید',
            'help' => 'بکشید و رها کنید یا از کتابخانه انتخاب کنید',
          ])
          <input name="url" value="{{ $item->url }}" placeholder="لینک (اختیاری)" dir="ltr">
          <label class="field">
            <span>ترتیب نمایش</span>
            <input type="number" name="sort_order" value="{{ $item->sort_order }}" min="0">
          </label>
          <label class="inline"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> فعال</label>
          <button class="btn btn-ghost" type="submit">ذخیره</button>
        </form>
        <form method="post" action="{{ route('admin.partners.destroy', $item) }}" data-confirm="حذف این لوگو؟">
          @csrf @method('delete')
          <button class="btn btn-danger" type="submit">حذف</button>
        </form>
      </article>
    @empty
      <div class="empty">لوگویی ثبت نشده است.</div>
    @endforelse
  </div>
  @include('admin.partials.media-catalog')
@endsection
