<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'داشبورد') | آلوین</title>
  <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}?v=3.4">
</head>
<body class="app-body">
  <div class="scrim" data-sidebar-scrim hidden></div>
  @include('admin.partials.media-library-modal')
  <div class="app">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-top">
        <a class="brand" href="{{ route('admin.dashboard') }}">
          <span class="brand-mark">A</span>
          <span class="brand-copy">
            <strong>آلوین</strong>
            <small>استودیوی محتوا</small>
          </span>
        </a>

        <nav class="side-nav" aria-label="منوی اصلی">
          <div class="nav-group">شروع</div>
          <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z"/></svg>
            </span>
            <span class="nav-label">نمای کلی</span>
          </a>

          <div class="nav-group">محتوا</div>
          <a href="{{ route('admin.homepage') }}" class="{{ request()->routeIs('admin.homepage') || (request()->routeIs('admin.pages.*') && optional(request()->route('page'))->key === 'home') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z"/></svg>
            </span>
            <span class="nav-label">صفحه اصلی</span>
          </a>
          <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M3 10h18M9 4v16"/></svg>
            </span>
            <span class="nav-label">محصولات</span>
          </a>
          <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
            </span>
            <span class="nav-label">دسته‌ها</span>
          </a>
          <a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="7" height="6" rx="1.5"/><rect x="14" y="5" width="7" height="6" rx="1.5"/><rect x="3" y="13" width="7" height="6" rx="1.5"/><rect x="14" y="13" width="7" height="6" rx="1.5"/></svg>
            </span>
            <span class="nav-label">پروژه‌ها</span>
          </a>
          <a href="{{ route('admin.articles.index') }}" class="{{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h9l5 5v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/><path d="M14 4v5h5M8 13h8M8 17h5"/></svg>
            </span>
            <span class="nav-label">مقالات</span>
          </a>
          <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v5h5"/></svg>
            </span>
            <span class="nav-label">صفحات</span>
          </a>
          <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
            </span>
            <span class="nav-label">رسانه</span>
          </a>

          <div class="nav-group">سایت</div>
          <a href="{{ route('admin.inquiries.index') }}" class="{{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H7l-3 3V5z"/></svg>
            </span>
            <span class="nav-label">سفارش‌ها</span>
            @if(($newInquiryCount ?? 0) > 0)
              <em class="nav-count">{{ $newInquiryCount }}</em>
            @endif
          </a>
          <a href="{{ route('admin.pricing.index') }}" class="{{ request()->routeIs('admin.pricing.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M8 17h8"/><rect x="3" y="4" width="18" height="16" rx="3"/></svg>
            </span>
            <span class="nav-label">قیمت‌گذاری</span>
          </a>
          <a href="{{ route('admin.partners.index') }}" class="{{ request()->routeIs('admin.partners.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3"/><path d="M5 19a7 7 0 0 1 14 0"/></svg>
            </span>
            <span class="nav-label">مشتریان</span>
          </a>
          <a href="{{ route('admin.settings.edit', ['screen' => 'calculator']) }}" class="{{ request()->routeIs('admin.settings.*') && request('screen') === 'calculator' ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
            </span>
            <span class="nav-label">متن ماشین‌حساب</span>
          </a>
          <a href="{{ route('admin.settings.edit', ['screen' => 'seo']) }}" class="{{ request()->routeIs('admin.settings.*') && request('screen') === 'seo' ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="6"/><path d="m20 20-3.5-3.5"/></svg>
            </span>
            <span class="nav-label">سئو</span>
          </a>
          <a href="{{ route('admin.navigation.index') }}" class="{{ request()->routeIs('admin.navigation.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
            </span>
            <span class="nav-label">منو و ناوبری</span>
          </a>
          <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') && request('screen', 'general') === 'general' ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9c.3.6.9 1 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
            </span>
            <span class="nav-label">تنظیمات عمومی</span>
          </a>
          <a href="{{ route('admin.activity.index') }}" class="{{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
            <span class="nav-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 7v5l3 2"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <span class="nav-label">تاریخچه</span>
          </a>

          @if(auth()->user()->isSuperAdmin())
            <div class="nav-group">سیستم</div>
            <a href="{{ route('admin.system.check') }}" class="{{ request()->routeIs('admin.system.*') ? 'active' : '' }}">
              <span class="nav-ico" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v3M12 18v3M4.9 6.5l2.1 2.1M17 15.4l2.1 2.1M3 12h3M18 12h3M4.9 17.5 7 15.4M17 8.6l2.1-2.1"/><circle cx="12" cy="12" r="4"/></svg>
              </span>
              <span class="nav-label">سلامت سیستم</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
              <span class="nav-ico" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="3"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </span>
              <span class="nav-label">کاربران</span>
            </a>
          @endif
        </nav>
      </div>

      <div class="sidebar-foot">
        <div class="sidebar-user">
          <span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
          <div>
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ auth()->user()->roleLabel() }} · {{ \App\Support\BuildInfo::label() }}</small>
          </div>
        </div>
        <a class="sidebar-site" href="/" target="_blank" rel="noopener">
          مشاهده سایت
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7M9 7h8v8"/></svg>
        </a>
      </div>
    </aside>

    <div class="main">
      <header class="topbar">
        <button class="icon-btn" type="button" data-sidebar-toggle aria-label="منو">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <div class="topbar-search">
          <span class="live-pill">
            <span class="live-dot" aria-hidden="true"></span>
            ویرایش زنده · {{ parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'local' }}
          </span>
        </div>
        <div class="topbar-actions">
          <a class="btn btn-ghost btn-sm topbar-site" href="/" target="_blank" rel="noopener">پیش‌نمایش سایت</a>
          <div class="user-pill">
            <span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
            <span class="user-pill__name">{{ auth()->user()->name }}</span>
          </div>
          <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn btn-quiet" type="submit">خروج</button>
          </form>
        </div>
      </header>

      <div class="content fly-stage">
        @if(session('success'))
          <div class="toast toast-ok" role="status" data-auto-dismiss>
            <span class="toast-ico" aria-hidden="true">✓</span>
            <span>{{ session('success') }}</span>
          </div>
        @endif
        @if($errors->any())
          <div class="toast toast-error" role="alert" data-auto-dismiss>
            <span class="toast-ico" aria-hidden="true">!</span>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif
        @yield('content')
      </div>
    </div>
  </div>
  @stack('boot')
  <script src="{{ asset('assets/admin/js/admin.js') }}?v=3.2"></script>
  @stack('scripts')
</body>
</html>
