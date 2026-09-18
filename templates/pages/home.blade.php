@php
    use App\Support\Site;
    $intro = $siteSettings['intro_src'] ?? 'assets/Video/intro.mp4';
    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'VideoObject',
        'name' => $blocks['hero_video_name'] ?? 'ویدیو معرفی آلوین پنجره',
        'description' => $page->seo_description ?? 'معرفی تولید و نصب پنجره دوجداره UPVC آلوین',
        'contentUrl' => url($intro),
        'embedUrl' => url('/'),
        'encodingFormat' => 'video/mp4',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<x-layouts.app :page="$page" :json-ld="$jsonLd" cms-page="home">
  <x-slot:scripts>
    <script src="{{ asset('assets/js/alwin-intro.js') }}?v=1.3"></script>
  </x-slot:scripts>

  <section class="hero-area">
    <div class="container large">
      <div class="hero-area-inner section-spacing-top">
        <div class="hero-content section-spacing-bottom">
          <div class="award-wrapper fade-anim" data-delay="0.90" data-direction="left" data-ease="back.out(4)">
            <div class="circle-text-wrapper alwin-circle-wrap">
              <div class="circle-text alwin-circle">
                <img src="{{ asset('assets/img/brand/logo-wrapper-text.png') }}" alt="" class="alwin-circle-ring" width="180" height="180">
                <img src="{{ asset('assets/img/brand/logo-mini.png') }}" alt="" class="alwin-circle-icon" width="72" height="72">
              </div>
            </div>
          </div>
          <div class="section-header">
            <div class="section-title-wrapper">
              <div class="title-wrapper">
                <h2 class="section-title" data-cms="hero_title" data-cms-html="true">{!! $blocks['hero_title'] ?? 'تولید و نصب پنجره دوجداره UPVC<br>با کیفیت و قیمت مناسب' !!}</h2>
              </div>
            </div>
          </div>
          <div class="section-content">
            <div class="text-wrapper fade-anim" data-delay="0.75">
              <p class="text" data-cms="hero_text">{{ $blocks['hero_text'] ?? 'پیشرو در تولید و نصب پنجره و درب دوجداره UPVC با استانداردهای اروپایی، قیمت شفاف و مشاوره رایگان.' }}</p>
            </div>
          </div>
        </div>
        <div class="big-text-wrapper">
          <h2 class="big-text" dir="ltr">ALWIN</h2>
        </div>
      </div>
    </div>
  </section>

  <section class="about-area">
    <div class="container large">
      <div class="about-area-inner section-spacing">
        <div class="section-content">
          <div class="shape-1"></div>
          <div class="shape-2"></div>
          <div class="shape-3"></div>
          <div class="shape-4"></div>
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h2 class="section-title" data-cms="about_title">{{ $blocks['about_title'] ?? 'ALWIN' }}</h2>
            </div>
          </div>
          <div class="text-wrapper">
            <p class="text" data-cms="about_text">{{ $blocks['about_text'] ?? 'با بیش از ۱۵ سال تجربه در تولید، نصب و خدمات پس از فروش پنجره و درب UPVC، ALWIN همراه شماست از انتخاب مدل تا نصب نهایی.' }}</p>
          </div>
          <div class="btn-wrapper">
            <a href="{{ route('about') }}" class="rr-btn btn-text-fli hover-bg-theme">
              <span class="btn-wrap">
                <span class="text-one" data-cms="about_button">{{ $blocks['about_button'] ?? 'بیشتر بدانید' }}</span>
                <span class="text-two" data-cms="about_button">{{ $blocks['about_button'] ?? 'بیشتر بدانید' }}</span>
              </span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="video-box alwin-intro" data-alwin-intro>
    <video class="video-area" loop muted autoplay playsinline preload="metadata" fetchpriority="low">
      <source src="{{ asset($intro) }}" type="video/mp4">
    </video>
    <button type="button" class="alwin-intro__toggle" aria-pressed="true" aria-label="{{ $blocks['intro_pause_label'] ?? 'توقف ویدیو' }}" data-pause-label="{{ $blocks['intro_pause_label'] ?? 'توقف ویدیو' }}" data-play-label="{{ $blocks['intro_play_label'] ?? 'پخش ویدیو' }}">
      <svg class="alwin-intro__rings" viewBox="0 0 64 64" aria-hidden="true">
        <circle class="alwin-intro__ring alwin-intro__ring--track" cx="32" cy="32" r="29"></circle>
        <circle class="alwin-intro__ring alwin-intro__ring--buffered" cx="32" cy="32" r="29" pathLength="100"></circle>
        <circle class="alwin-intro__ring alwin-intro__ring--played" cx="32" cy="32" r="29" pathLength="100"></circle>
      </svg>
      <span class="alwin-intro__glyph">
        <svg class="alwin-intro__icon alwin-intro__icon--pause" viewBox="0 0 24 24" aria-hidden="true">
          <rect x="6" y="5" width="4" height="14" rx="1"></rect>
          <rect x="14" y="5" width="4" height="14" rx="1"></rect>
        </svg>
        <svg class="alwin-intro__icon alwin-intro__icon--play" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M8 5.5v13l11-6.5z"></path>
        </svg>
      </span>
    </button>
  </div>

  @include('partials.partners-marquee')

  <section class="work-area">
    <div class="container large">
      <div class="work-area-inner">
        <div class="section-header fade-anim">
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h2 class="section-title" data-cms="work_title">{{ $blocks['work_title'] ?? 'پروژه‌های اجرا شده' }}</h2>
            </div>
          </div>
          <div class="total-count">
            <span class="number">({{ Site::fa($projectCount) }})</span>
          </div>
        </div>
        <div class="works-wrapper-box">
          <div class="works-wrapper-1 fade-anim">
            @foreach($projects as $project)
              <div class="work-box">
                <div class="thumb">
                  <div class="image scale">
                    <a href="{{ route('projects.show', $project) }}">
                      <picture>
                        <source type="image/webp" srcset="{{ $project->image?->publicUrl() }}">
                        <img src="{{ $project->image?->publicUrl() }}" alt="{{ $project->alt_text ?: $project->title }}" width="800" height="600" loading="lazy">
                      </picture>
                    </a>
                  </div>
                </div>
                <div class="content">
                  <h3 class="title"><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h3>
                  <div class="meta">
                    <span class="tag">{{ $project->type_label }}</span>
                    <span class="date">{{ $project->location }}</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
        <div class="all-btn-wrapper fade-anim">
          <a href="{{ route('projects.index') }}" class="rr-btn btn-border hover-bg-theme">
            <span class="btn-wrap">
              <span class="text-one" data-cms="work_all_button">{{ $blocks['work_all_button'] ?? 'مشاهده همه پروژه‌ها' }}</span>
              <span class="text-two" data-cms="work_all_button">{{ $blocks['work_all_button'] ?? 'مشاهده همه پروژه‌ها' }}</span>
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="service-area">
    <div class="container large">
      <div class="service-area-inner section-spacing">
        <div class="section-header">
          <div class="section-title-wrapper fade-anim">
            <div class="title-wrapper">
              <h2 class="section-title" data-cms="products_title">{{ $blocks['products_title'] ?? 'محصولات' }}</h2>
            </div>
          </div>
        </div>
        <div class="services-wrapper-box">
          <div class="services-wrapper-1">
            @foreach($products as $i => $product)
              @php
                $close = $product->imageClose?->publicUrl();
                $open = $product->imageOpen?->publicUrl() ?: $close;
              @endphp
              <div class="service-box fade-anim">
                <div class="count">
                  <span class="number">({{ Site::fa(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) }})</span>
                </div>
                <div class="content">
                  <h3 class="title"><a href="{{ route('products') }}">{{ $product->name }}</a></h3>
                  <ul class="service-list">
                    <li><a href="{{ route('products') }}">{{ $product->category?->name }}</a></li>
                  </ul>
                </div>
                <div class="thumb">
                  <div class="home-product-media">
                    <picture>
                      <source type="image/webp" srcset="{{ $close }}">
                      <img src="{{ $close }}" class="home-product-media__img home-product-media__img--close grow" alt="{{ $product->alt_text ?: $product->name }}" width="800" height="600" loading="lazy" decoding="async">
                    </picture>
                    <picture>
                      <source type="image/webp" srcset="{{ $open }}">
                      <img src="{{ $open }}" class="home-product-media__img home-product-media__img--open" alt="" aria-hidden="true" width="800" height="600" loading="lazy" decoding="async">
                    </picture>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="client-area">
    <div class="container large">
      <div class="client-area-inner section-spacing-top">
        <div class="section-content">
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h2 class="section-title" data-cms="clients_title"><span>{{ $blocks['clients_title'] ?? 'برند های ما' }}</span></h2>
            </div>
          </div>
        </div>
        <div class="client-capsule-wrapper-box" data-t-throwable-scene="true">
          <div class="client-capsule-wrapper">
            @foreach($partners as $i => $partner)
              <p data-t-throwable-el="">
                <span class="client-box {{ $i % 2 === 1 ? 'bg-theme' : '' }}">
                  <picture>
                    <source type="image/webp" srcset="{{ $partner->image?->publicUrl() }}">
                    <img src="{{ $partner->image?->publicUrl() }}" alt="{{ $partner->alt_text ?: $partner->name }}" width="260" height="110" loading="lazy" decoding="async">
                  </picture>
                </span>
              </p>
            @endforeach
          </div>
        </div>
        <div class="lines-wrapper">
          @for($i = 0; $i < 8; $i++)
            <div class="line"></div>
          @endfor
        </div>
      </div>
    </div>
  </section>

  <section class="cta-area cta-area--static" aria-labelledby="home-cta-title">
    <div class="cta-area-inner">
      <div class="container large">
        <div class="alwin-cta">
          <span class="alwin-cta__orb alwin-cta__orb--a" aria-hidden="true"></span>
          <span class="alwin-cta__orb alwin-cta__orb--b" aria-hidden="true"></span>
          <div class="alwin-cta__copy">
            <p class="alwin-cta__kicker">
              <span class="alwin-cta__live" aria-hidden="true"></span>
              <span data-cms="cta_kicker">{{ $blocks['cta_kicker'] ?? 'برآورد آنلاین و رایگان' }}</span>
            </p>
            <h2 id="home-cta-title" class="alwin-cta__title" data-cms="cta_title">{{ $blocks['cta_title'] ?? 'قیمت پنجره‌تان را همین حالا ببینید' }}</h2>
            <p class="alwin-cta__hint" data-cms="cta_hint">{{ $blocks['cta_hint'] ?? 'ابعاد را وارد کنید؛ نتیجه را در لحظه ببینید' }}</p>
          </div>
          <div class="alwin-cta__action">
            <span class="alwin-cta__btn-glow" aria-hidden="true"></span>
            <button type="button" class="alwin-cta__btn" data-calculator-open>
              <span class="alwin-cta__btn-label">{{ $siteSettings['cta_label'] ?? 'محاسبه قیمت' }}</span>
              <span class="alwin-cta__btn-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                  <path d="M19 12H5M11 6l-6 6 6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="productivity-area">
    <div class="container large">
      <div class="productivity-area-inner section-spacing">
        <div class="section-content">
          <div class="section-title-wrapper">
            <div class="title-wrapper">
              <h2 class="section-title" data-cms="principles_title" data-cms-html="true">{!! $blocks['principles_title'] ?? 'کیفیت، دقت و <br> قیمت منصفانه — <span>سه اصل</span> <span>ALWIN</span> در <span>هر پروژه</span>' !!}</h2>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-layouts.app>
