@php
    use App\Support\Site;
    $settings = $siteSettings ?? [];
    $email = $settings['email'] ?? 'info@alwinco.ir';
    $phone = $settings['phone'] ?? '09909777090';
    $logoUrl = Site::mediaUrl($settings['logo'] ?? null, 'assets/img/brand/logo.png');
    $social = [
        ['instagram', 'اینستاگرام', 'fa-brands fa-instagram'],
        ['telegram', 'تلگرام', 'fa-brands fa-telegram'],
        ['whatsapp', 'واتساپ', 'fa-brands fa-whatsapp'],
        ['aparat', 'آپارات', 'fa-brands fa-play'],
        ['linkedin', 'لینکدین', 'fa-brands fa-linkedin-in'],
    ];
@endphp
<footer class="footer-area alwin-footer">
  <div class="container large">
    <div class="alwin-footer__inner">
      <div class="alwin-footer__grid">
        <div class="alwin-footer__brand">
          <a class="alwin-footer__logo" href="{{ route('home') }}">
            <img src="{{ $logoUrl }}" alt="{{ $settings['site_name'] ?? 'آلوین' }}">
          </a>
          <p class="alwin-footer__tagline">{{ $settings['tagline'] ?? 'آلوین — تولیدکننده و نصاب پنجره و درب دوجداره UPVC' }}</p>
          <ul class="alwin-footer__contact">
            <li>
              <i class="fa-solid fa-envelope" aria-hidden="true"></i>
              <a href="mailto:{{ $email }}">{{ $email }}</a>
            </li>
            <li>
              <i class="fa-solid fa-phone" aria-hidden="true"></i>
              <a href="{{ Site::phoneHref($phone) }}" dir="ltr">{{ $settings['phone_display'] ?? $phone }}</a>
            </li>
            <li>
              <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
              <span>{{ $settings['address'] ?? '' }}</span>
            </li>
          </ul>
        </div>
        <div class="alwin-footer__col">
          <h2 class="alwin-footer__title">{{ $settings['footer_links_title'] ?? 'دسترسی سریع' }}</h2>
          <ul class="alwin-footer__links">
            @foreach($footerNav as $item)
              <li><a href="{{ $item->href }}">{{ $item->label }}</a></li>
            @endforeach
          </ul>
        </div>
        <div class="alwin-footer__col">
          <h2 class="alwin-footer__title">{{ $settings['footer_social_title'] ?? 'شبکه‌های اجتماعی' }}</h2>
          <div class="alwin-footer__social" aria-label="{{ $settings['footer_social_title'] ?? 'شبکه‌های اجتماعی' }}">
            @foreach($social as [$key, $label, $icon])
              @php
                $href = trim((string) ($settings[$key] ?? ''));
                $core = in_array($key, ['instagram', 'telegram', 'whatsapp'], true);
                $custom = $href !== '' && ! in_array($href, ['/contact', '#'], true);
              @endphp
              @if($core || $custom)
                <a class="alwin-footer__social-link" href="{{ Site::href($href !== '' ? $href : '/contact') }}" aria-label="{{ $label }}">
                  <i class="{{ $icon }}" aria-hidden="true"></i>
                </a>
              @endif
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="alwin-footer__bottom">
    <div class="container large">
      <div class="alwin-footer__bottom-inner">
        <p class="alwin-footer__copy">{{ $settings['copyright'] ?? '© ۲۰۲۶ آلوین. تمامی حقوق محفوظ است.' }}</p>
        @if(!empty($settings['credit']))
          <p class="alwin-footer__credit">{{ $settings['credit'] }}</p>
        @endif
      </div>
    </div>
  </div>
</footer>
