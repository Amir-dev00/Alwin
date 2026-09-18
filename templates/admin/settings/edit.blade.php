@extends('admin.layout')
@php
  $screen = $screen ?? 'general';
  $screenNames = [
    'general' => 'تنظیمات عمومی',
    'seo' => 'سئو پیش‌فرض',
    'calculator' => 'متن ماشین‌حساب',
  ];
@endphp
@section('title', $screenNames[$screen] ?? 'تنظیمات')
@section('content')
  @php
    $groupNames = [
      'contact' => 'اطلاعات تماس',
      'brand' => 'نام، لوگو و شعار',
      'seo' => 'سئو پیش‌فرض',
      'video' => 'ویدیوی معرفی',
      'social' => 'شبکه‌های اجتماعی',
      'footer' => 'پایین سایت و منو',
      'calculator' => 'متن ماشین‌حساب قیمت',
    ];
    $helps = [
      'email' => 'همان ایمیلی که در فوتر سایت دیده می‌شود.',
      'phone' => 'شماره تماس. نمایش روی سایت هم از روی همین پر می‌شود.',
      'phone_display' => 'اگر خالی باشد همان شماره بالا کپی می‌شود.',
      'address' => 'آدرس دفتر که در تماس و فوتر می‌آید.',
      'office_phones' => 'هر خط یک شماره. در صفحات تماس و درباره نمایش داده می‌شود.',
      'factory_title' => 'عنوان کارت کارخانه در صفحه درباره.',
      'factory_address' => 'آدرس کارخانه صفادشت.',
      'factory_phones' => 'هر خط یک شماره کارخانه.',
      'website_display' => 'متنی که به‌جای آدرس سایت دیده می‌شود.',
      'site_name' => 'نام کوتاه برند.',
      'tagline' => 'جمله معرفی زیر لوگو در فوتر.',
      'cta_label' => 'متن دکمه «محاسبه قیمت» در هدر و بنر خانه.',
      'logo' => 'لوگوی هدر و فوتر. اگر فایلی نگذارید همان لوگوی فعلی می‌ماند.',
      'favicon' => 'آیکون تب مرورگر.',
      'seo_description_default' => 'اگر صفحه توضیح گوگل نداشته باشد از این استفاده می‌شود.',
      'og_image' => 'تصویری که در اشتراک تلگرام/واتساپ دیده می‌شود.',
      'intro_src' => 'معمولاً لازم نیست عوض شود مگر ویدیو را عوض کرده باشید.',
      'instagram' => 'لینک کامل پیج، مثلاً https://instagram.com/... — مسیر داخلی مثل /contact هم مجاز است.',
      'telegram' => 'لینک کانال یا پیام‌رسان.',
      'whatsapp' => 'لینک واتساپ یا شماره با https://wa.me/',
      'aparat' => 'لینک کانال آپارات.',
      'linkedin' => 'لینک صفحه لینکدین.',
      'copyright' => 'متن کوچک پایین سایت.',
      'credit' => 'نام طراح؛ اگر نمی‌خواهید دیده شود خالی بگذارید.',
      'footer_links_title' => 'عنوان ستون لینک‌های فوتر.',
      'footer_social_title' => 'عنوان ستون شبکه‌های اجتماعی.',
      'sidebar_contact_title' => 'عنوان تماس در منوی موبایل.',
      'header_menu_open' => 'متن دسترسی‌پذیری دکمه همبرگر.',
      'header_menu_close' => 'متن دسترسی‌پذیری بستن منو.',
    ];
    $leads = [
      'general' => 'تلفن، آدرس، لوگو و متن‌های مشترک. متن صفحه اصلی را از «صفحه اصلی» عوض کنید.',
      'seo' => 'عنوان و توضیح پیش‌فرض گوگل و تصویر اشتراک‌گذاری.',
      'calculator' => 'فقط متن‌های دیده می‌شود. فرمول و نرخ‌ها در «قیمت‌گذاری» است.',
    ];
  @endphp
  <form method="post" action="{{ route('admin.settings.update', ['screen' => $screen]) }}" class="easy-form" data-unsaved data-easy-form enctype="multipart/form-data">
    @csrf @method('put')
    <div class="page-head">
      <div>
        <p class="page-kicker">سایت</p>
        <h1>{{ $screenNames[$screen] ?? 'تنظیمات سایت' }}</h1>
        <p>{{ $leads[$screen] ?? '' }}</p>
      </div>
      <button class="btn btn-primary" type="submit">ذخیره تنظیمات</button>
    </div>
    <nav class="inline-row" style="margin-bottom:1.25rem;flex-wrap:wrap;gap:.5rem">
      <a class="btn {{ $screen === 'general' ? 'btn-primary' : 'btn-ghost' }} btn-sm" href="{{ route('admin.settings.edit') }}">تنظیمات عمومی</a>
      <a class="btn {{ $screen === 'seo' ? 'btn-primary' : 'btn-ghost' }} btn-sm" href="{{ route('admin.settings.edit', ['screen' => 'seo']) }}">سئو</a>
      <a class="btn {{ $screen === 'calculator' ? 'btn-primary' : 'btn-ghost' }} btn-sm" href="{{ route('admin.settings.edit', ['screen' => 'calculator']) }}">متن ماشین‌حساب</a>
    </nav>
    @foreach($groups as $group => $rows)
      <section class="card easy-card" id="group-{{ $group }}">
        <header><h2>{{ $groupNames[$group] ?? $group }}</h2></header>
        <div class="form-grid">
          @foreach($rows as $row)
            <label class="field {{ in_array($row->type, ['textarea'], true) ? 'wide' : '' }}">
              <span>{{ $row->label ?: $row->key }}</span>
              @if($row->type === 'textarea')
                <textarea name="settings[{{ $row->id }}]" rows="4" data-setting-key="{{ $row->key }}">{{ $row->value }}</textarea>
              @elseif(in_array($row->type, ['image', 'file'], true))
                <input type="hidden" name="settings[{{ $row->id }}]" value="{{ $row->value }}">
                @if($row->value)
                  <img src="{{ \App\Support\Site::mediaUrl($row->value, ltrim($row->value, '/')) }}" alt="" width="160" style="max-width:160px;display:block;margin-bottom:8px;border-radius:8px">
                @endif
                <input type="file" name="setting_files[{{ $row->id }}]" accept=".webp,.jpg,.jpeg,.png,.gif,.bmp,image/*">
                @if($row->value)
                  <small class="field-help">
                    <label class="inline"><input type="checkbox" name="setting_clear[{{ $row->id }}]" value="1"> حذف و بازگشت به تصویر پیش‌فرض</label>
                  </small>
                @endif
              @else
                <input name="settings[{{ $row->id }}]" type="text" value="{{ $row->value }}" data-setting-key="{{ $row->key }}" @if(in_array($row->key, ['instagram','telegram','whatsapp','aparat','linkedin','email'], true) || str_contains((string) $row->value, 'http')) dir="ltr" @endif>
              @endif
              @if(!empty($helps[$row->key]))
                <small class="field-help">{{ $helps[$row->key] }}</small>
              @endif
            </label>
          @endforeach
        </div>
      </section>
    @endforeach
    <div class="save-dock" data-save-dock>
      <span data-save-hint>پس از ذخیره، سایت همان لحظه متن تازه را نشان می‌دهد.</span>
      <button class="btn btn-primary" type="submit">ذخیره تنظیمات</button>
    </div>
  </form>
@endsection
