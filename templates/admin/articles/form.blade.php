@extends('admin.layout')
@section('title', $item->exists ? 'ویرایش مقاله' : 'مقاله جدید')
@section('content')
  <form method="post" action="{{ $item->exists ? route('admin.articles.update', $item) : route('admin.articles.store') }}" class="easy-form" data-unsaved data-easy-form enctype="multipart/form-data">
    @csrf
    @if($item->exists) @method('put') @endif
    <div class="page-head fly-item">
      <div>
        <p class="page-kicker">{{ $item->exists ? 'ویرایش' : 'افزودن' }}</p>
        <h1>{{ $item->exists ? $item->title : 'مقاله جدید' }}</h1>
      </div>
      <div class="page-head-actions">
        @if($item->exists)
          <a class="btn btn-ghost" href="{{ route('articles.show', $item) }}" target="_blank" rel="noopener">دیدن در سایت</a>
        @endif
        <button class="btn btn-primary" type="submit">ذخیره مقاله</button>
      </div>
    </div>

    <section class="card easy-card fly-item" style="--i:1">
      <header>
        <div>
          <span class="step-no">۱</span>
          <h2>متن مقاله</h2>
        </div>
      </header>
      <div class="form-grid">
        <label class="wide field">
          <span>عنوان <em>الزامی</em></span>
          <input name="title" value="{{ old('title', $item->title) }}" required data-slug-source data-fill-source="title">
        </label>
        <label class="wide field">
          <span>خلاصه</span>
          <textarea name="excerpt" rows="3">{{ old('excerpt', $item->excerpt) }}</textarea>
        </label>
        <label class="wide field">
          <span>متن کامل (HTML)</span>
          <textarea name="body" class="rich" rows="16">{{ old('body', $item->body) }}</textarea>
        </label>
        <label class="wide field">
          <span>تصویر کاور</span>
          @if($item->coverUrl())
            <img src="{{ $item->coverThumbUrl() ?: $item->coverUrl() }}" alt="{{ $item->cover_alt }}" width="200" style="max-width:200px;border-radius:8px;margin-bottom:8px;display:block">
          @endif
          <input type="file" name="cover" accept=".webp,.jpg,.jpeg,.png,.gif,.bmp,.tif,.tiff,image/*">
          <small class="field-help">JPG، PNG، GIF یا WebP — به‌صورت WebP بهینه ذخیره می‌شود.</small>
        </label>
        <label class="field">
          <span>نویسنده</span>
          <input name="author" value="{{ old('author', $item->author) }}">
        </label>
        <label class="field">
          <span>تاریخ انتشار</span>
          <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($item->published_at)?->format('Y-m-d\TH:i')) }}">
        </label>
      </div>
    </section>

    <section class="card easy-card fly-item" style="--i:2">
      <header>
        <div>
          <span class="step-no">۲</span>
          <h2>انتشار</h2>
        </div>
      </header>
      <div class="switch-stack">
        <label class="switch-card">
          <input type="hidden" name="status" value="{{ old('status', $item->status) }}" data-status-input>
          <input type="checkbox" data-status-switch @checked(old('status', $item->status)==='published')>
          <span>
            <strong>در سایت دیده شود</strong>
            <small>اگر خاموش باشد فقط در پنل می‌ماند.</small>
          </span>
        </label>
        <label class="switch-card">
          <input type="checkbox" name="show_on_listing" value="1" @checked(old('show_on_listing', $item->show_on_listing ?? true))>
          <span>
            <strong>در صفحه مقالات نمایش داده شود</strong>
            <small>اگر خاموش باشد لینک مقاله کار می‌کند، اما در فهرست مقالات دیده نمی‌شود.</small>
          </span>
        </label>
      </div>
    </section>

    <section class="card easy-card fly-item" style="--i:3">
      <header>
        <div>
          <span class="step-no">۳</span>
          <h2>جستجوی گوگل</h2>
        </div>
      </header>
      <div class="form-grid">
        <label class="field">
          <span>عنوان گوگل</span>
          <input name="seo_title" value="{{ old('seo_title', $item->seo_title) }}" maxlength="70">
        </label>
        <label class="wide field">
          <span>توضیح گوگل</span>
          <textarea name="seo_description" rows="2" maxlength="160">{{ old('seo_description', $item->seo_description) }}</textarea>
        </label>
        <label class="field">
          <span>آدرس اینترنتی</span>
          <input name="slug" value="{{ old('slug', $item->slug) }}" data-slug-target dir="ltr">
        </label>
        <label class="field">
          <span>متن عکس</span>
          <input name="cover_alt" value="{{ old('cover_alt', $item->cover_alt) }}">
        </label>
      </div>
    </section>

    <div class="save-dock" data-save-dock>
      <span data-save-hint>عنوان و متن را وارد کنید.</span>
      <button class="btn btn-primary" type="submit">ذخیره مقاله</button>
    </div>
  </form>
@endsection
