@extends('admin.layout')
@section('title', $item->exists ? 'ویرایش پروژه' : 'پروژه جدید')
@section('content')
  <form method="post" action="{{ $item->exists ? route('admin.projects.update', $item) : route('admin.projects.store') }}" class="easy-form" data-unsaved data-easy-form>
    @csrf
    @if($item->exists) @method('put') @endif
    <div class="page-head fly-item">
      <div>
        <p class="page-kicker">{{ $item->exists ? 'ویرایش' : 'افزودن' }}</p>
        <h1>{{ $item->exists ? $item->title : 'پروژه جدید' }}</h1>
        <p>عنوان، عکس و محل پروژه کافی است. گوگل و آدرس صفحه را خودمان می‌سازیم.</p>
      </div>
      <div class="page-head-actions">
        @if($item->exists)
          <a class="btn btn-ghost" href="{{ route('projects.show', $item) }}" target="_blank" rel="noopener">دیدن در سایت</a>
        @endif
        <button class="btn btn-primary" type="submit">ذخیره پروژه</button>
      </div>
    </div>

    <section class="card easy-card fly-item" style="--i:1">
      <header>
        <div>
          <span class="step-no">۱</span>
          <h2>درباره این پروژه</h2>
        </div>
      </header>
      <div class="form-grid">
        <label class="wide field">
          <span>عنوان پروژه <em>الزامی</em></span>
          <input name="title" value="{{ old('title', $item->title) }}" required data-slug-source data-fill-source="title" placeholder="مثال: برج مسکونی سعادت‌آباد">
          <small class="field-help">همین عنوان در سایت و گوگل دیده می‌شود.</small>
        </label>
        <label class="field">
          <span>نوع کار</span>
          <input name="type_label" value="{{ old('type_label', $item->type_label) }}" data-fill-source="type-label" placeholder="پنجره UPVC" list="project-types">
          <datalist id="project-types">
            <option value="پنجره UPVC">
            <option value="درب UPVC">
            <option value="نمای کرتین وال">
            <option value="جام بالکن">
            <option value="توری پنجره">
          </datalist>
        </label>
        <label class="field">
          <span>محل</span>
          <input name="location" value="{{ old('location', $item->location) }}" placeholder="تهران، سعادت‌آباد">
        </label>
        <label class="field">
          <span>کارفرما</span>
          <input name="client_name" value="{{ old('client_name', $item->client_name) }}" placeholder="اختیاری">
        </label>
        <label class="wide field">
          <span>خلاصه کوتاه</span>
          <textarea name="summary" rows="3" data-fill-source="summary" placeholder="در دو خط بگویید چه کاری انجام شد.">{{ old('summary', $item->summary) }}</textarea>
        </label>
        <label class="wide field">
          <span>توضیح کامل</span>
          <textarea name="description" class="rich" rows="8">{{ old('description', $item->description) }}</textarea>
        </label>
      </div>
    </section>

    <section class="card easy-card fly-item" style="--i:2">
      <header>
        <div>
          <span class="step-no">۲</span>
          <h2>عکس پروژه</h2>
          <p>یک عکس افقی کافی است</p>
        </div>
      </header>
      @include('admin.partials.media-picker', [
        'name' => 'image_id',
        'label' => 'عکس کاور',
        'collection' => 'projects',
        'value' => old('image_id', $item->image_id),
        'preview' => $item->image?->publicUrl(),
        'filename' => $item->image?->original_name,
        'hint' => 'عکس پروژه را اینجا بیندازید',
        'help' => 'بهتر است کمی عریض‌تر از ارتفاع باشد',
      ])
    </section>

    <section class="card easy-card fly-item" style="--i:3">
      <header>
        <div>
          <span class="step-no">۳</span>
          <h2>در سایت چه کار کند؟</h2>
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
          <input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $item->show_on_home))>
          <span>
            <strong>در صفحه نخست هم بیاید</strong>
            <small>برای پروژه‌های شاخص.</small>
          </span>
        </label>
        <label class="switch-card">
          <input type="checkbox" name="show_on_listing" value="1" @checked(old('show_on_listing', $item->show_on_listing ?? true))>
          <span>
            <strong>در صفحه پروژه‌ها نمایش داده شود</strong>
            <small>اگر خاموش باشد لینک پروژه کار می‌کند، اما در فهرست پروژه‌ها دیده نمی‌شود.</small>
          </span>
        </label>
      </div>
    </section>

    <section class="card easy-card fly-item" style="--i:4">
      <header>
        <div>
          <span class="step-no">۴</span>
          <h2>جستجوی گوگل</h2>
          <p>خودکار از روی عنوان پر می‌شود</p>
        </div>
      </header>
      <div class="serp" data-serp>
        <div class="serp-url">{{ parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'local' }} › پروژه‌ها</div>
        <div class="serp-title" data-serp-title>{{ old('seo_title', $item->seo_title) ?: ($item->title ?: 'عنوان پروژه | آلوین') }}</div>
        <div class="serp-desc" data-serp-desc>{{ old('seo_description', $item->seo_description) ?: 'خلاصه پروژه اینجا دیده می‌شود.' }}</div>
      </div>
      <div class="form-grid">
        <label class="field">
          <span>عنوان گوگل <i class="auto-pill">خودکار</i></span>
          <input name="seo_title" value="{{ old('seo_title', $item->seo_title) }}" maxlength="70" data-autofill="seo-title" data-serp-bind="title" data-count>
          <small class="field-help field-count" data-count-out></small>
        </label>
        <label class="wide field">
          <span>توضیح گوگل <i class="auto-pill">خودکار</i></span>
          <textarea name="seo_description" rows="2" maxlength="160" data-autofill="seo-desc" data-serp-bind="desc" data-count>{{ old('seo_description', $item->seo_description) }}</textarea>
          <small class="field-help field-count" data-count-out></small>
        </label>
      </div>
    </section>

    <details class="card easy-card advanced-card">
      <summary>تنظیمات فنی (لازم نیست باز کنید)</summary>
      <div class="form-grid" style="margin-top:16px">
        <label class="field">
          <span>آدرس اینترنتی</span>
          <input name="slug" value="{{ old('slug', $item->slug) }}" data-slug-target data-autofill="slug" dir="ltr">
        </label>
        <label class="field">
          <span>کد نوع</span>
          <input name="type" value="{{ old('type', $item->type) }}" data-autofill="type-key" dir="ltr">
        </label>
        <label class="field">
          <span>متن عکس</span>
          <input name="alt_text" value="{{ old('alt_text', $item->alt_text) }}" data-autofill="alt">
        </label>
        <label class="field">
          <span>ترتیب نمایش</span>
          <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
        </label>
      </div>
    </details>

    <div class="save-dock" data-save-dock>
      <span data-save-hint>عنوان و عکس را بگذارید؛ بقیه را خودمان پر می‌کنیم.</span>
      <button class="btn btn-primary" type="submit">ذخیره پروژه</button>
    </div>
  </form>
  @include('admin.partials.media-catalog')
@endsection
