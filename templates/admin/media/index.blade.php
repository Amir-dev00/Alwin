@extends('admin.layout')
@section('title', 'رسانه')
@section('content')
  <div class="page-head">
    <div>
      <h1>عکس‌ها و ویدیوها</h1>
      <p>فایل را بکشید و رها کنید. توضیح عکس اگر خالی بماند از نام فایل پر می‌شود.</p>
    </div>
  </div>
  <div class="grid-2">
    <section class="card">
      <header><h2>بارگذاری</h2></header>
      <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="stack" data-upload>
        @csrf
        <div class="dropzone dropzone--rich" data-dropzone>
          <input type="file" name="file" accept=".webp,.jpg,.jpeg,.png,.gif,.mp4,.webm,image/*,video/mp4,video/webm" required hidden data-dropzone-input>
          <div class="dropzone__icon" aria-hidden="true">
            <svg viewBox="0 0 48 48" width="42" height="42" fill="none">
              <path d="M24 8v22M16 16l8-8 8 8" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
              <rect x="8" y="28" width="32" height="12" rx="4" stroke="currentColor" stroke-width="2"/>
            </svg>
          </div>
          <strong>فایل را بکشید و اینجا رها کنید</strong>
          <small>یا برای انتخاب از سیستم کلیک کنید · تصویر و ویدیو · حداکثر ۵۰ مگابایت</small>
          <div class="dropzone__file" hidden data-dropzone-name></div>
        </div>
        <label><span>توضیح عکس (اختیاری)</span><input name="alt" placeholder="اگر خالی باشد از نام فایل پر می‌شود" data-media-alt></label>
        <div class="upload-progress" hidden><span></span></div>
        <button class="btn btn-primary" type="submit">بارگذاری</button>
      </form>
    </section>
    <section class="card">
      <form class="filters" method="get">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="جستجوی نام فایل…">
        <select name="kind">
          <option value="">همه انواع</option>
          <option value="image" @selected(request('kind')==='image')>تصویر</option>
          <option value="video" @selected(request('kind')==='video')>ویدیو</option>
        </select>
        <button class="btn btn-ghost" type="submit">اعمال</button>
      </form>
    </section>
  </div>
  <div class="media-grid">
    @forelse($items as $m)
      <article class="media-card">
        @if($m->kind === 'image')
          <img src="{{ $m->thumbnailUrl() }}" alt="{{ $m->alt }}">
        @else
          <div class="video-chip">ویدیو</div>
        @endif
        <figcaption>
          <strong>{{ $m->original_name }}</strong>
          <small>{{ $m->kind }} · {{ number_format($m->size / 1024, 0) }} کیلوبایت</small>
          @if($m->isUsed())
            <span class="pill published">در حال استفاده</span>
          @else
            <span class="pill draft">بدون استفاده</span>
          @endif
        </figcaption>
        <form method="post" action="{{ route('admin.media.update', $m) }}" enctype="multipart/form-data" class="stack tight" data-upload>
          @csrf @method('put')
          <input name="alt" value="{{ $m->alt }}" placeholder="توضیح عکس">
          <div class="dropzone dropzone--compact" data-dropzone>
            <input type="file" name="file" accept=".webp,.jpg,.jpeg,.png,.gif,.mp4,.webm,image/*,video/mp4,video/webm" hidden data-dropzone-input>
            <strong>تعویض فایل</strong>
            <small>بکشید یا کلیک کنید</small>
            <div class="dropzone__file" hidden data-dropzone-name></div>
          </div>
          <div class="upload-progress" hidden><span></span></div>
          <button class="btn btn-ghost" type="submit">به‌روزرسانی</button>
        </form>
        @unless($m->isUsed())
          <form method="post" action="{{ route('admin.media.destroy', $m) }}" data-confirm="این فایل حذف شود؟ دیگر در سایت دیده نمی‌شود.">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit">حذف</button>
          </form>
        @endunless
      </article>
    @empty
      <div class="empty">رسانه‌ای یافت نشد.</div>
    @endforelse
  </div>
  {{ $items->links() }}
@endsection
