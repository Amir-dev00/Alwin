@extends('admin.layout')
@section('title', 'ویرایش '.$page->title)
@section('content')
  <form method="post" action="{{ route('admin.pages.update', $page) }}" class="easy-form" data-unsaved data-easy-form>
    @csrf @method('put')
    <div class="page-head">
      <div>
        <p class="page-kicker">متن صفحه</p>
        <h1>{{ $page->title }}</h1>
        <p>فقط متن‌ها را عوض کنید. ظاهر سایت عوض نمی‌شود.</p>
      </div>
      <div class="page-head-actions">
        <a class="btn btn-ghost" href="/{{ ltrim($page->path, '/') }}" target="_blank" rel="noopener">دیدن صفحه</a>
        <button class="btn btn-primary" type="submit">ذخیره صفحه</button>
      </div>
    </div>
    <section class="card easy-card">
      <header>
        <div>
          <h2>جستجوی گوگل</h2>
          <p>اگر خالی بماند از عنوان صفحه پر می‌شود</p>
        </div>
      </header>
      <div class="serp" data-serp>
        <div class="serp-url">{{ parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'local' }} › {{ $page->title }}</div>
        <div class="serp-title" data-serp-title>{{ old('seo_title', $page->seo_title) }}</div>
        <div class="serp-desc" data-serp-desc>{{ old('seo_description', $page->seo_description) }}</div>
      </div>
      <div class="form-grid">
        <label class="field">
          <span>نام داخلی این صفحه</span>
          <input name="title" value="{{ old('title', $page->title) }}" required data-fill-source="title">
        </label>
        <label class="field">
          <span>عنوان گوگل <i class="auto-pill">خودکار</i></span>
          <input name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" maxlength="70" data-autofill="seo-title" data-serp-bind="title" data-count>
          <small class="field-help field-count" data-count-out></small>
        </label>
        <label class="wide field">
          <span>توضیح گوگل</span>
          <textarea name="seo_description" rows="2" maxlength="160" data-serp-bind="desc" data-count>{{ old('seo_description', $page->seo_description) }}</textarea>
          <small class="field-help field-count" data-count-out></small>
        </label>
      </div>
    </section>
    <section class="card easy-card">
      <header>
        <div>
          <h2>متن‌های این صفحه</h2>
          <p>برای جابه‌جایی، نقطهچین را بکشید</p>
        </div>
      </header>
      <div class="blocks" data-sortable>
        @foreach($page->blocks as $block)
          <article class="block" draggable="true" data-id="{{ $block->id }}">
            <input type="hidden" name="order[]" value="{{ $block->id }}">
            <div class="block-head">
              <span class="drag" aria-hidden="true">⋮⋮</span>
              <strong>{{ $block->label ?: $block->key }}</strong>
            </div>
            @if($block->type === 'html')
              <textarea name="blocks[{{ $block->id }}][value]" class="rich" rows="6">{{ $block->value }}</textarea>
            @elseif($block->type === 'textarea')
              <textarea name="blocks[{{ $block->id }}][value]" rows="4">{{ $block->value }}</textarea>
            @else
              <input name="blocks[{{ $block->id }}][value]" value="{{ $block->value }}">
            @endif
            <input type="hidden" name="blocks[{{ $block->id }}][id]" value="{{ $block->id }}">
          </article>
        @endforeach
      </div>
    </section>
    <div class="save-dock" data-save-dock>
      <span data-save-hint>متن را عوض کنید و ذخیره بزنید. همان لحظه روی سایت می‌آید.</span>
      <button class="btn btn-primary" type="submit">ذخیره صفحه</button>
    </div>
  </form>
@endsection
