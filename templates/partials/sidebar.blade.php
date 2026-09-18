@php
    use App\Support\Site;
    $settings = $siteSettings ?? [];
    $phone = $settings['phone'] ?? '09909777090';
    $email = $settings['email'] ?? 'info@alwinco.ir';
    $cta = $settings['cta_label'] ?? 'محاسبه قیمت';
    $logoUrl = Site::mediaUrl($settings['logo'] ?? null, 'assets/img/brand/logo.png');
@endphp
<aside class="fix">
  <div class="side-info" id="alwin-side-info">
    <div class="side-info-content">
      <div class="offset-widget offset-header">
        <div class="offset-logo">
          <a href="{{ route('home') }}">
            <img src="{{ $logoUrl }}" alt="{{ $settings['site_name'] ?? 'ALWIN' }}">
          </a>
        </div>
        <button id="side-info-close" class="side-info-close" type="button" aria-label="{{ $settings['header_menu_close'] ?? 'بستن منو' }}">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="mobile-menu d-xl-none fix"></div>
      <div class="offset-button">
        <button type="button" class="rr-btn hover-bg-theme" data-calculator-open>
          <span class="btn-wrap">
            <span class="text-one">{{ $cta }}</span>
            <span class="text-two">{{ $cta }}</span>
          </span>
        </button>
      </div>
      <div class="offset-widget-box">
        <h2 class="title">{{ $settings['sidebar_contact_title'] ?? 'تماس با ما' }}</h2>
        <div class="contact-meta">
          <div class="contact-item">
            <span class="icon"><i class="fa-solid fa-envelope"></i></span>
            <span class="text"><a href="mailto:{{ $email }}">{{ $email }}</a></span>
          </div>
          <div class="contact-item">
            <span class="icon"><i class="fa-solid fa-phone"></i></span>
            <span class="text"><a href="{{ Site::phoneHref($phone) }}">{{ $settings['phone_display'] ?? $phone }}</a></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</aside>
<div class="offcanvas-overlay"></div>
