@php
    use App\Support\Site;
    $cta = $siteSettings['cta_label'] ?? 'محاسبه قیمت';
    $current = url()->current();
    $logoUrl = Site::mediaUrl($siteSettings['logo'] ?? null, 'assets/img/brand/logo.png');
@endphp
<header class="header-area">
  <div class="header-main">
    <div class="container large">
      <div class="header-area__inner">
        <div class="header__logo">
          <a href="{{ route('home') }}">
            <img src="{{ $logoUrl }}" class="normal-logo" alt="{{ $siteSettings['site_name'] ?? 'ALWIN' }}" width="160" height="48">
          </a>
        </div>
        <div class="header__nav">
          <nav class="main-menu" aria-label="منوی اصلی">
            <ul>
              @foreach($headerNav as $item)
                @php $active = rtrim($current, '/') === rtrim($item->href, '/'); @endphp
                <li @class(['is-current' => $active])>
                  <a href="{{ $item->href }}">{{ $item->label }}</a>
                </li>
              @endforeach
            </ul>
          </nav>
        </div>
        <div class="header__button">
          <button type="button" class="rr-btn hover-bg-theme" data-calculator-open>
            <span class="btn-wrap">
              <span class="text-one">{{ $cta }}</span>
              <span class="text-two">{{ $cta }}</span>
            </span>
          </button>
        </div>
        <div class="header__navicon d-xl-none">
          <button class="side-toggle" type="button" aria-expanded="false" aria-controls="alwin-side-info" aria-label="{{ $siteSettings['header_menu_open'] ?? 'باز کردن منو' }}">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</header>
