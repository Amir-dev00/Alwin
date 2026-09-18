@extends('admin.layout')
@section('title', 'نمای کلی')
@section('content')
  @php
    $hour = now()->hour;
    $hello = $hour < 12 ? 'صبح بخیر' : ($hour < 18 ? 'سلام' : 'عصر بخیر');
  @endphp

  <section class="dash-hero fly-item">
    <div class="dash-hero__copy">
      <p class="dash-kicker">{{ $hello }}</p>
      <h1>{{ auth()->user()->name }}، آماده‌ای پرواز کنیم؟</h1>
      <p>همه‌چیز برای مدیریت زنده سایت آلوین اینجاست — سریع، شفاف، و بدون اصطکاک.</p>
    </div>
    <div class="dash-hero__actions">
      <a class="btn btn-primary" href="{{ route('admin.products.create') }}">
        <span>+ محصول جدید</span>
      </a>
      <a class="btn btn-ghost" href="{{ route('admin.projects.create') }}">پروژه جدید</a>
      <a class="btn btn-quiet" href="/" target="_blank" rel="noopener">مشاهده سایت</a>
    </div>
  </section>

  @if(($stats['new_inquiries'] ?? 0) > 0)
    <a class="dash-alert fly-item" style="--i:1" href="{{ route('admin.inquiries.index', ['status' => 'new']) }}">
      <strong>{{ $stats['new_inquiries'] }} درخواست تماس جدید</strong>
      <span>مشتری فرم را پر کرده — برای پیگیری سفارش باز کنید.</span>
    </a>
  @endif

  <section class="stats dash-stats">
    <article class="fly-item" style="--i:1">
      <div class="stat-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M3 10h18"/></svg>
      </div>
      <span>محصولات</span>
      <strong>{{ $stats['products_total'] }}</strong>
      <small>{{ $stats['products_published'] }} منتشر · {{ $stats['products_draft'] }} پیش‌نویس</small>
    </article>
    <article class="fly-item" style="--i:2">
      <div class="stat-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="7" height="6" rx="1.5"/><rect x="14" y="5" width="7" height="6" rx="1.5"/><rect x="3" y="13" width="7" height="6" rx="1.5"/><rect x="14" y="13" width="7" height="6" rx="1.5"/></svg>
      </div>
      <span>پروژه‌ها</span>
      <strong>{{ $stats['projects_total'] }}</strong>
      <small>{{ $stats['projects_published'] }} منتشر · {{ $stats['projects_draft'] }} پیش‌نویس</small>
    </article>
    <article class="fly-item" style="--i:3">
      <div class="stat-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
      </div>
      <span>رسانه</span>
      <strong>{{ $stats['media'] }}</strong>
      <small>فایل در کتابخانه</small>
    </article>
    <article class="fly-item {{ $stats['missing_images'] ? 'warn' : 'ok' }}" style="--i:4">
      <div class="stat-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 4.3 1.8 19a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0z"/></svg>
      </div>
      <span>هشدار تصویر</span>
      <strong>{{ $stats['missing_images'] }}</strong>
      <small>{{ $stats['missing_images'] ? 'نیاز به تکمیل تصویر' : 'همه تصاویر کامل‌اند' }}</small>
    </article>
    <article class="fly-item {{ $stats['incomplete_seo'] ? 'warn' : 'ok' }}" style="--i:5">
      <div class="stat-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
      </div>
      <span>سئوی ناقص</span>
      <strong>{{ $stats['incomplete_seo'] }}</strong>
      <small>{{ $stats['incomplete_seo'] ? 'عنوان یا توضیح سئو کم است' : 'سئو در وضعیت خوب' }}</small>
    </article>
  </section>

  <section class="dash-shortcuts fly-item" style="--i:6">
    <a class="shortcut" href="{{ route('admin.inquiries.index') }}">
      <strong>سفارش‌ها</strong>
      <span>{{ ($stats['new_inquiries'] ?? 0) ? $stats['new_inquiries'].' درخواست جدید' : 'فرم تماس و درخواست تماس' }}</span>
    </a>
    <a class="shortcut" href="{{ route('admin.pages.index') }}">
      <strong>ویرایش صفحات</strong>
      <span>متن‌های خانه، درباره و تماس</span>
    </a>
    <a class="shortcut" href="{{ route('admin.media.index') }}">
      <strong>بارگذاری رسانه</strong>
      <span>تصاویر و ویدیو با درگ‌اند‌دراپ</span>
    </a>
    <a class="shortcut" href="{{ route('admin.pricing.index') }}">
      <strong>موتور قیمت</strong>
      <span>پروفیل، شیشه، یراق و آزمایش‌گر زنده</span>
    </a>
    <a class="shortcut" href="{{ route('admin.settings.edit') }}">
      <strong>تنظیمات تماس</strong>
      <span>تلفن، ایمیل و شبکه‌های اجتماعی</span>
    </a>
    <a class="shortcut" href="{{ route('admin.navigation.index') }}">
      <strong>منوی سایت</strong>
      <span>لینک‌های هدر و فوتر</span>
    </a>
  </section>

  <div class="grid-2 dash-panels">
    <section class="card fly-item" style="--i:7">
      <header>
        <div>
          <h2>آخرین تغییرات</h2>
          <p>نبض فعالیت تیم محتوا</p>
        </div>
        <a class="link-more" href="{{ route('admin.activity.index') }}">همه</a>
      </header>
      <div class="timeline">
        @forelse($recentLogs as $log)
          <div class="timeline-item">
            <span class="timeline-dot" aria-hidden="true"></span>
            <div>
              <strong>{{ $log->description ?: $log->action }}</strong>
              <small>{{ $log->user?->name }} · {{ $log->created_at?->diffForHumans() }}</small>
            </div>
          </div>
        @empty
          <div class="empty empty-soft">هنوز فعالیتی ثبت نشده است.</div>
        @endforelse
      </div>
    </section>

    <section class="card fly-item" style="--i:8">
      <header>
        <div>
          <h2>رسانه‌های تازه</h2>
          <p>آخرین فایل‌های کتابخانه</p>
        </div>
        <a class="link-more" href="{{ route('admin.media.index') }}">کتابخانه</a>
      </header>
      <div class="media-rail">
        @forelse($recentMedia as $m)
          <figure class="media-tile">
            @if($m->kind === 'image')
              <img src="{{ $m->thumbnailUrl() }}" alt="{{ $m->alt }}">
            @else
              <div class="video-chip">ویدیو</div>
            @endif
            <figcaption>{{ \Illuminate\Support\Str::limit($m->original_name, 22) }}</figcaption>
          </figure>
        @empty
          <div class="empty empty-soft">فایلی بارگذاری نشده.</div>
        @endforelse
      </div>

      <div class="system-strip">
        <div>
          <span>نقش شما</span>
          <strong>{{ auth()->user()->roleLabel() }}</strong>
        </div>
        <div>
          <span>آخرین ورود</span>
          <strong>{{ auth()->user()->last_login_at?->timezone('Asia/Tehran')->format('Y/m/d H:i') ?: '—' }}</strong>
        </div>
      </div>
    </section>
  </div>
@endsection
