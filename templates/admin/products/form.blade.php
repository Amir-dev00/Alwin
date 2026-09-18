@extends('admin.layout')
@section('title', $item->exists ? 'ویرایش محصول' : 'محصول جدید')
@section('content')
  <form method="post" action="{{ $item->exists ? route('admin.products.update', $item) : route('admin.products.store') }}" class="easy-form" data-unsaved data-easy-form>
    @csrf
    @if($item->exists) @method('put') @endif
    <div class="page-head fly-item">
      <div>
        <p class="page-kicker">{{ $item->exists ? 'ویرایش' : 'افزودن' }}</p>
        <h1>{{ $item->exists ? $item->name : 'محصول جدید' }}</h1>
        <p>فقط نام، توضیح و دو عکس را پر کنید. بقیه را خودمان می‌نویسیم.</p>
      </div>
      <div class="page-head-actions">
        @if($item->exists)
          <a class="btn btn-ghost" href="/services.html" target="_blank">دیدن در سایت</a>
        @endif
        <button class="btn btn-primary" type="submit">ذخیره محصول</button>
      </div>
    </div>

    <section class="card easy-card fly-item" style="--i:1">
      <header>
        <div>
          <span class="step-no">۱</span>
          <h2>این محصول چیست؟</h2>
          <p>نامی که مشتری در سایت می‌بیند</p>
        </div>
      </header>
      <div class="form-grid">
        <label class="wide field">
          <span>نام محصول <em>الزامی</em></span>
          <input name="name" value="{{ old('name', $item->name) }}" required data-slug-source data-fill-source="title" placeholder="مثال: پنجره لولایی دو حالته">
          <small class="field-help">کوتاه و واضح بنویسید. بقیه فیلدها از روی همین پر می‌شوند.</small>
        </label>
        <label class="field">
          <span>دسته</span>
          <select name="category_id" data-fill-source="category">
            <option value="">انتخاب کنید</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}" data-product-type="{{ str_contains($c->name, 'توری') ? 'window_screen' : (str_contains($c->name, 'در') ? 'door_or_window' : 'window') }}" @selected(old('category_id', $item->category_id)==$c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
          <small class="field-help">پنجره و درب را از توری جدا می‌کند.</small>
        </label>
        <label class="wide field">
          <span>توضیح کوتاه</span>
          <textarea name="short_description" rows="3" data-fill-source="summary" placeholder="در دو سه خط بگویید این محصول چه کمکی می‌کند.">{{ old('short_description', $item->short_description) }}</textarea>
          <small class="field-help">همین متن برای توضیح گوگل هم استفاده می‌شود مگر اینکه عوضش کنید.</small>
        </label>
        <label class="wide field">
          <span>توضیح کامل</span>
          <textarea name="description" class="rich" rows="8">{{ old('description', $item->description) }}</textarea>
        </label>
        <label class="wide field">
          <span>نکات و مشخصات</span>
          <textarea name="specifications_text" rows="4" placeholder="هر نکته را در یک خط بنویسید
عایق صدا
گاز آرگون بین شیشه‌ها">{{ old('specifications_text', implode("\n", $item->specifications ?? [])) }}</textarea>
          <small class="field-help">هر خط یک مورد می‌شود. لازم نیست همه را پر کنید.</small>
        </label>
      </div>
    </section>

    <section class="card easy-card fly-item" style="--i:2">
      <header>
        <div>
          <span class="step-no">۲</span>
          <h2>عکس باز و بسته</h2>
          <p>دو عکس از یک زاویه؛ یکی بسته، یکی باز</p>
        </div>
      </header>
      <div class="pair pair-media">
        @include('admin.partials.media-picker', [
          'name' => 'image_close_id',
          'label' => 'پنجره بسته',
          'badge' => 'بسته',
          'collection' => 'products',
          'value' => old('image_close_id', $item->image_close_id),
          'preview' => $item->imageClose?->publicUrl(),
          'filename' => $item->imageClose?->original_name,
          'hint' => 'عکس بسته را اینجا بیندازید',
          'help' => 'همان قاب و نور عکس باز',
        ])
        @include('admin.partials.media-picker', [
          'name' => 'image_open_id',
          'label' => 'پنجره باز',
          'badge' => 'باز',
          'collection' => 'products',
          'value' => old('image_open_id', $item->image_open_id),
          'preview' => $item->imageOpen?->publicUrl(),
          'filename' => $item->imageOpen?->original_name,
          'hint' => 'عکس باز را اینجا بیندازید',
          'help' => 'همان زاویه عکس بسته',
        ])
      </div>
    </section>

    <section class="card easy-card fly-item" style="--i:3">
      <header>
        <div>
          <span class="step-no">۳</span>
          <h2>در سایت چه کار کند؟</h2>
          <p>با یک کلید روشن یا خاموش کنید</p>
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
            <small>برای محصولات مهم‌تر.</small>
          </span>
        </label>
        <label class="switch-card">
          <input type="checkbox" name="show_on_listing" value="1" @checked(old('show_on_listing', $item->show_on_listing ?? true))>
          <span>
            <strong>در صفحه محصولات نمایش داده شود</strong>
            <small>اگر خاموش باشد در فهرست محصولات دیده نمی‌شود.</small>
          </span>
        </label>
      </div>
    </section>

    <section class="card easy-card fly-item" style="--i:4">
      <header>
        <div>
          <span class="step-no">۴</span>
          <h2>جستجوی گوگل</h2>
          <p>خودکار از روی نام و توضیح پر می‌شود</p>
        </div>
      </header>
      <div class="serp" data-serp>
        <div class="serp-url">{{ parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'local' }} › محصولات</div>
        <div class="serp-title" data-serp-title>{{ old('seo_title', $item->seo_title) ?: ($item->name ?: 'عنوان محصول | آلوین') }}</div>
        <div class="serp-desc" data-serp-desc>{{ old('seo_description', $item->seo_description) ?: 'توضیح کوتاه محصول اینجا دیده می‌شود.' }}</div>
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
          <span>متن عکس (برای نابینایان)</span>
          <input name="alt_text" value="{{ old('alt_text', $item->alt_text) }}" data-autofill="alt">
        </label>
        <label class="field">
          <span>ترتیب نمایش</span>
          <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
        </label>
        <label class="field">
          <span>شماره پوشه تصویر</span>
          <input type="number" name="folder_number" value="{{ old('folder_number', $item->folder_number) }}">
        </label>
        <label class="field">
          <span>کد نوع</span>
          <input name="product_type" value="{{ old('product_type', $item->product_type) }}" data-autofill="product-type" dir="ltr">
        </label>
      </div>
    </details>

    <div class="save-dock" data-save-dock>
      <span data-save-hint>وقتی آماده بودید ذخیره کنید — بقیه فیلدها خودکار پر می‌شوند.</span>
      <button class="btn btn-primary" type="submit">ذخیره محصول</button>
    </div>
  </form>
  @include('admin.partials.media-catalog')
@endsection
